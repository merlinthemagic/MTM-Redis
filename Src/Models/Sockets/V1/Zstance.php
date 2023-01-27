<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets\V1;

class Zstance extends Tracking
{
	protected $_id=null;
	protected $_inMulti=false;
	protected $_sockObj=null;
	protected $_readLag=250000; //we want to run fast, but not waste too many cpu cycles
	protected $_maxReadLag=250000;
	
	public function getId()
	{
		return $this->_id;
	}
	public function inMulti()
	{
		return $this->_inMulti;
	}
	public function setMulti($bool)
	{
		if (is_bool($bool) === false) {
			throw new \Exception("Invalid input");
		}
		$this->_inMulti		= $bool;
		return $this;
	}
	public function write($strCmd)
	{
		$cmdParts	= str_split($strCmd, $this->getClient()->getChunkSize());
		$sockRes	= $this->getSocket();
		if (is_resource($sockRes) === false) {
			throw new \Exception("Cannot write, socket is not a resource");
		}
		foreach ($cmdParts as $cmdPart) {
			$written	= fwrite($sockRes, $cmdPart);
			if (strlen($cmdPart) !== $written) {
				throw new \Exception("Failed to write command. Wrote: ".$written.", expected: ".strlen($cmdPart));
			}
		}
		return $this;
	}
	public function read($throw=false, $timeout=5000)
	{
		$timeFact	= \MTM\Utilities\Factories::getTime();
		$sTime		= $timeFact->getMicroEpoch();
		$tTime		= $sTime + ($timeout / 1000);
		$rData		= "";
		$dCount		= 0;
		$sockRes	= $this->getSocket();
		if (is_resource($sockRes) === false) {
			throw new \Exception("Cannot read, socket is not a resource");
		}
		while(true) {
			$data 	= fgets($sockRes);
			if ($data !== false) {
				$rData	.= $data;
				$dCount++;
			} elseif ($rData != "" && substr($rData, -2) == "\r\n") {
				
				//check if the first part indicates a string return.
				//redis writes e.g. $164223\r\n to hint a string is inbound, but may then pause to fetch the full key value
				//we dont want to return before redis has gotten past the initial stages
				if (preg_match("/^\\\$([0-9]+)\r\n/", substr($rData, 0, 23), $rLen) === 1) {
					
					if (strlen($rData) >= $rLen[1]) {
						
						//adjust the lag to reflect how long this took to handle
						//this will avg out the round trip time to the redis server over time
						$this->_readLag		= intval(($this->_readLag + (((($timeFact->getMicroEpoch() - $sTime)*1000000)) / $dCount)) / 2);
						if ($this->_readLag > $this->_maxReadLag) {
							$this->_readLag		= $this->_maxReadLag;
						}
						return $rData;
						
					} elseif ($tTime < $timeFact->getMicroEpoch()) {
						if ($throw === true) {
							throw new \Exception("Partial read. Command timeout");
						} else {
							return null;
						}
					}
					
				} else {
					return $rData;
				}
				
			} elseif ($tTime < $timeFact->getMicroEpoch()) {
				if ($throw === true) {
					throw new \Exception("Read command timeout");
				} else {
					return null;
				}
			} else {
				usleep($this->_readLag);
			}
		}
	}
	public function getSocket()
	{
		return $this->_sockObj;
	}
	public function initialize()
	{
		if ($this->isInit() === false) {
			$cliObj		= $this->getClient();
			if ($cliObj->getProtocol() === null) {
				throw new \Exception("Client protocol is not set");
			} elseif ($cliObj->getHostname() == "") {
				throw new \Exception("Client hostname is not set");
			} elseif ($cliObj->getPort() === null) {
				throw new \Exception("Client port is not set");
			}
			
			if ($cliObj->getSslCert() === null) {
				$strConn	= $cliObj->getProtocol()."://".$cliObj->getHostname().":".$cliObj->getPort()."/";
			} else {
				//steal logic from wsSocket client
				throw new \Exception("Not yet handled for tls");
			}
	
			//cannot use persistant clients, the auth sequence gets scred up 
			$sockRes 	= stream_socket_client($strConn, $errno, $errstr, $cliObj->getTimeout(), STREAM_CLIENT_CONNECT);
			if (is_resource($sockRes) === false) {
				throw new \Exception("Socket Error: " . $errstr, $errno);
			}
			
			stream_set_blocking($sockRes, false);
			stream_set_chunk_size($sockRes, $cliObj->getChunkSize());

			$this->_sockObj	= $sockRes;
			
			if ($this->getClient()->getAuth() != "") {
				
				try {
					$this->auth($cliObj->getAuth())->exec(true);
				} catch (\Exception $e) {
					fclose($this->_sockObj);
					$this->_sockObj	= null;
					throw $e;
				}
			}
			
			try {
				$this->_id	= $this->clientId()->exec(true);
			} catch (\Exception $e) {
				fclose($this->_sockObj);
				$this->_sockObj	= null;
				throw $e;
			}
			
			$this->setInit();
		}
		return $this;
	}
	public function terminate($throw=false)
	{
		if ($this->isInit() === true && $this->isTerm() === false) {
			$this->read(false, -1); //clear the socket before quitting
			$this->quit()->exec($throw);
			fclose($this->_sockObj);
			$this->setTerm();
		}
		return $this;
	}
}