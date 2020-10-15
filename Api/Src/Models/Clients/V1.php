<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients;

class V1 extends Base
{
	protected $_protocol=null;
	protected $_hostname=null;
	protected $_portNbr=null;
	protected $_authStr=null;
	protected $_timeout=30;
	protected $_sslCertObj=null;
	protected $_sslAllowSelfSigned=false;
	protected $_sslVerifyPeer=true;
	protected $_sslVerifyPeerName=true;
	protected $_chunkSize=4096;
	protected $_dbId=0;
	protected $_mainSockObj=null;
	protected $_chSockObj=null;
	protected $_readBuffer="";
	protected $_chanObjs=array();
	
	public function getChannels()
	{
		return array_values($this->_chanObjs);
	}
	public function addChannel($name, $isPattern=false)
	{
		if ($this->getChannelByName($name, false) !== null) {
			throw new \Exception("Channel already exist: ".$name);
		}
		//remember to call subscribe()
		if ($isPattern === false) {
			$chObj	= new \MTM\RedisApi\Models\Channels\V1($this, $name);
		} else {
			$chObj	= new \MTM\RedisApi\Models\Channels\V2($this, $name);
		}
		
		$this->_chanObjs[$chObj->getGuid()]	= $chObj;
		return $chObj;
	}
	public function removeChannel($chanObj)
	{
		if (array_key_exists($chanObj->getGuid(), $this->_chanObjs) === true) {
			unset($this->_chanObjs[$chanObj->getGuid()]);
			$chanObj->unsubscribe();
		} else {
			throw new \Exception("Channel does not belong to this client");
		}
	}
	public function getChannelByName($name, $throw=false)
	{
		foreach ($this->_chanObjs as $chanObj) {
			if ($chanObj->getName() == $name) {
				return $chanObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("Channel not subscribed: ".$name);
		} else {
			return null;
		}
	}
	public function setConnection($protocol, $hostname, $portNbr, $auth=null, $timeout=30)
	{
		$this->_protocol	= $protocol;
		$this->_hostname	= $hostname;
		$this->_portNbr		= $portNbr;
		$this->_authStr		= $auth;
		$this->_timeout		= $timeout;
		return $this;
	}
	public function setSslConnection($certObj=null, $verifyPeer=true, $verifyPeerName=true, $allowSelfSigned=false)
	{
		if ($certObj !== null && $certObj instanceof \MTM\Certs\Models\CRT === false) {
			//should be a certificate object containing enough of the chain to confirm the server authenticity
			throw new \Exception("Invalid Certificate");
		} else {
			$this->_sslCertObj			= $certObj;
			$this->_sslVerifyPeer		= $verifyPeer;
			$this->_sslVerifyPeerName	= $verifyPeerName;
			$this->_sslAllowSelfSigned	= $allowSelfSigned;
		}
		return $this;
	}
	public function setDatabase($id)
	{
		if ($this->_dbId !== $id) {
			if (is_int($id) === false) {
				throw new \Exception("Invalid database id");
			}
			
			$cmdStr		= "*2\r\n\$6\r\nSELECT\r\n\$".strlen($id)."\r\n".$id."\r\n";
			$this->socketWrite($this->getMainSocket(), $cmdStr);

			$rData		= $this->mainSocketRead(true);
			if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
				$this->_dbId	= $id;
				return $this;
			} elseif (strpos($rData, "-ERR") === 0) {
				throw new \Exception("Error: ".$rData);
			} else {
				throw new \Exception("Not handled for return: ".$rData);
			}
		}
	}
	public function socketWrite($sockObj, $strCmd)
	{
		$cmdParts	= str_split($strCmd, $this->_chunkSize);
		foreach ($cmdParts as $cmdPart) {
			$written	= fwrite($sockObj, $cmdPart);
			if (strlen($cmdPart) != $written) {
				throw new \Exception("Failed to write command");
			}
		}
		return $this;
	}
	public function socketRead($sockObj, $throw=false, $timeout=5000)
	{
		$tTime		= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + ($timeout / 1000);
		$rData		= "";
		while(true) {
			$data 	= fgets($sockObj);
			if ($data != "") {
				$rData	.= $data;
			} elseif ($rData != "") {
				return $rData;
			} elseif ($tTime < \MTM\Utilities\Factories::getTime()->getMicroEpoch()) {
				if ($throw === true) {
					throw new \Exception("Read command timeout");
				} else {
					return null;
				}
			}
		}
	}
	public function mainSocketRead($throw=false, $timeout=5000)
	{
		return $this->socketRead($this->getMainSocket(), $throw, $timeout);
	}
	public function chanSocketRead($throw=false, $timeout=2000)
	{
		$rData	= $this->socketRead($this->getChanSocket(), $throw, $timeout);
		if (strpos($rData, "message\r\n") !== false) {
			
			$head	= "*3\r\n$7\r\nmessage\r\n";
			$hLen	= strlen($head);
			while(true) {
				$sPos	= strpos($rData, $head);
				if ($sPos !== false) {
					$reData		= $rData;
					$rData		= substr($rData, 0, $sPos);
					$reData		= substr($reData, ($sPos + $hLen));
					
					$nPos		= strpos($reData, "\r\n");
					$chanLen	= intval(substr($reData, 1, $nPos));
					$chanName	= substr($reData, ($nPos + 2), $chanLen);
					$reData		= substr($reData, ($nPos + $chanLen + 4));
					
					$nPos		= strpos($reData, "\r\n");
					$payLen		= intval(substr($reData, 1, $nPos));
					$payload	= substr($reData, ($nPos + 2), $payLen);
					$rData		.= substr($reData, ($nPos + $payLen + 4));
					
					$chanObj	= $this->getChannelByName($chanName, false);
					if ($chanObj !== null) {
						$chanObj->addMsg($payload);
					}
			
				} else {
					break;
				}
			}
			$head	= "*4\r\n$8\r\npmessage\r\n";
			$hLen	= strlen($head);
			while(true) {
				$sPos	= strpos($rData, $head);
				if ($sPos !== false) {
					$reData		= $rData;

					$rData		= substr($rData, 0, $sPos);
					$reData		= substr($reData, ($sPos + $hLen));
					
					$nPos		= strpos($reData, "\r\n");
					$patternLen	= intval(substr($reData, 1, $nPos));
					$pattern	= substr($reData, ($nPos + 2), $patternLen);
					$reData		= substr($reData, ($nPos + $patternLen + 4));
					
					$nPos		= strpos($reData, "\r\n");
					$chanLen	= intval(substr($reData, 1, $nPos));
					$chanName	= substr($reData, ($nPos + 2), $chanLen);
					$reData		= substr($reData, ($nPos + $chanLen + 4));
					
					$nPos		= strpos($reData, "\r\n");
					$payLen		= intval(substr($reData, 1, $nPos));
					$payload	= substr($reData, ($nPos + 2), $payLen);
					$rData		.= substr($reData, ($nPos + $payLen + 4));

					$chanObj	= $this->getChannelByName($pattern, false);
					if ($chanObj !== null) {
						$chanObj->addMsg($chanName, $payload);
					}
					
				} else {
					break;
				}
			}
		}
		return $rData;
	}
	protected function newSocket()
	{
		//src: https://redis.io/topics/pubsub
		//A client subscribed to one or more channels should not issue commands, 
		//although it can subscribe and unsubscribe to and from other channels.
		//this means channels must have their own dedicated socket
		if ($this->_sslCertObj === null) {
			$strConn	= $this->_protocol."://".$this->_hostname.":".$this->_portNbr."/";
		} else {
			//steal logic from wsSocket client
			throw new \Exception("Not yet handled for tls");
		}
		
		$sockRes 		= stream_socket_client($strConn, $errno, $errstr, $this->_timeout, STREAM_CLIENT_CONNECT);
		if (is_resource($sockRes) === true) {
			stream_set_blocking($sockRes, false);
			stream_set_chunk_size($sockRes, $this->_chunkSize);
			
			if ($this->_authStr != "") {
				$cmdStr		= "*2\r\n\$4\r\nAUTH\r\n\$".strlen($this->_authStr)."\r\n".$this->_authStr."\r\n";
				$this->socketWrite($sockRes, $cmdStr);
				$rData	= $this->socketRead($sockRes, true);
				if (preg_match("/(^\+OK\r\n)$/si", $rData) === 0) {
					if (strpos($rData, "-WRONGPASS") === 0) {
						throw new \Exception("Invalid password: ".$rData);
					} elseif (strpos($rData, "-ERR") === 0) {
						throw new \Exception("Error: ".$rData);
					} else {
						throw new \Exception("Not handled for return: ".$rData);
					}
				}
			}
			return $sockRes;
			
		} else {
			//if you get error: Address already in use, know that if the port was in use by another socket
			//that is now shutdown, it will take a few seconds before the port is available again
			//but it will be freed up eventually
			throw new \Exception("Socket Error: " . $errstr, $errno);
		}
	}
	public function getMainSocket()
	{
		if ($this->_mainSockObj === null) {
			$this->_mainSockObj	= $this->newSocket();
		}
		return $this->_mainSockObj;
	}
	public function getChanSocket()
	{
		if ($this->_chSockObj === null) {
			$this->_chSockObj	= $this->newSocket();
		}
		return $this->_chSockObj;
	}
	public function quit()
	{
		if ($this->_chSockObj !== null) {
			foreach ($this->getChannels() as $chanObj) {
				try {
					$this->removeChannel($chanObj);
				} catch (\Exception $e) {
					
				}
			}
			
			$cmdStr		= "*1\r\n\$4\r\nQUIT\r\n";
			$this->socketWrite($this->_chSockObj, $cmdStr);
			$rData		= $this->chanSocketRead(true);
			if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
				fclose($this->_chSockObj);
				$this->_chSockObj	= null;
			} elseif (strpos($rData, "-ERR") === 0) {
				throw new \Exception("Error: ".$rData);
			} else {
				throw new \Exception("Not handled for return: ".$rData);
			}
		}
		
		if ($this->_mainSockObj !== null) {
			$cmdStr		= "*1\r\n\$4\r\nQUIT\r\n";
			$this->socketWrite($this->_mainSockObj, $cmdStr);
			$rData		= $this->mainSocketRead(true);
			if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
				fclose($this->_mainSockObj);
				$this->_mainSockObj	= null;
			} elseif (strpos($rData, "-ERR") === 0) {
				throw new \Exception("Error: ".$rData);
			} else {
				throw new \Exception("Not handled for return: ".$rData);
			}
		}
		return $this;
	}
}