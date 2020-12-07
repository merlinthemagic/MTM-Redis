<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets\V1;

class Zstance extends Tracking
{
	protected $_id=null;
	protected $_inMulti=false;
	protected $_sockObj=null;
	protected $_readDelay=5000; //in micro secs
	
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
		foreach ($cmdParts as $cmdPart) {
			$written	= fwrite($this->getSocket(), $cmdPart);
			if (strlen($cmdPart) != $written) {
				throw new \Exception("Failed to write command");
			}
		}
		return $this;
	}
	public function read($throw=false, $timeout=5000)
	{
		$tTime		= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + ($timeout / 1000);
		$rData		= "";
		$lLen		= null;
		while(true) {
			$data 	= fgets($this->getSocket());
			if ($data !== false) {
				$rData	.= $data;
			} elseif ($rData != "") {
				
				$dLen	= strlen($rData);
				if ($dLen > 1400 && $lLen !== $dLen) {
					//intermittent failures when the sender is a bit slower for large payloads
					//seems 1448 is delivered consistently (max payload in the TCP frame?)
					//return would get cutoff and read by the next command, havoc
					$lLen	= $dLen;
					usleep($this->_readDelay);
					
				} else {
					return $rData;
				}

			} elseif ($tTime < \MTM\Utilities\Factories::getTime()->getMicroEpoch()) {
				if ($throw === true) {
					throw new \Exception("Read command timeout");
				} else {
					return null;
				}
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
			
			if ($this->getClient()->getSslCert() === null) {
				$strConn	= $this->getClient()->getProtocol()."://".$this->getClient()->getHostname().":".$this->getClient()->getPort()."/";
			} else {
				//steal logic from wsSocket client
				throw new \Exception("Not yet handled for tls");
			}
			
			$sockRes 		= stream_socket_client($strConn, $errno, $errstr, $this->getClient()->getTimeout(), STREAM_CLIENT_CONNECT);
			if (is_resource($sockRes) === false) {
				throw new \Exception("Socket Error: " . $errstr, $errno);
			}
			
			stream_set_blocking($sockRes, false);
			stream_set_chunk_size($sockRes, $this->getClient()->getChunkSize());
			
			$this->_sockObj	= $sockRes;
			
			if ($this->getClient()->getAuth() != "") {
				
				try {
					$this->auth($this->getClient()->getAuth())->exec(true);
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