<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients;

class V1 extends Base
{
	protected $_phpRedisObj=null;
	
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
	protected $_encoder="none";
	protected $_chanObjs=array();
	protected $_streamObjs=array();
	protected $_dbObjs=array();
	
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
	public function getChannels()
	{
		return array_values($this->_chanObjs);
	}
	public function addChannel($name)
	{
		if ($this->getChannelByName($name, false) !== null) {
			throw new \Exception("Channel already exist: ".$name);
		}
		//remember to call subscribe()
		$chObj	= new \MTM\RedisApi\Models\Channels\V1($this, $name);
		$this->_chanObjs[$chObj->getGuid()]	= $chObj;
		return $chObj;
	}
	public function addPatternChannel($name)
	{
		if ($this->getChannelByName($name, false) !== null) {
			throw new \Exception("Channel already exist: ".$name);
		}
		//remember to call subscribe()
		$chObj	= new \MTM\RedisApi\Models\Channels\V2($this, $name);
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
			throw new \Exception("Channel does not exist: ".$name);
		} else {
			return null;
		}
	}
	public function getStreams()
	{
		return array_values($this->_streamObjs);
	}
	public function addStream($key)
	{
		if ($this->getStreamByKey($key, false) !== null) {
			throw new \Exception("Stream already exist: ".$key);
		}
		$streamObj		= new \MTM\RedisApi\Models\Streams\V1($this, $key);
		$this->_streamObjs[$streamObj->getGuid()]	= $streamObj;
		return $streamObj;
	}
	public function removeStream($streamObj)
	{
		if (array_key_exists($streamObj->getGuid(), $this->_streamObjs) === true) {
			unset($this->_streamObjs[$streamObj->getGuid()]);
			$streamObj->terminate();
		} else {
			throw new \Exception("Stream does not belong to this client");
		}
	}
	public function getStreamByKey($key, $throw=false)
	{
		foreach ($this->_streamObjs as $streamObj) {
			if ($streamObj->getKey() == $key) {
				return $streamObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("Stream key does not exist: ".$key);
		} else {
			return null;
		}
	}
	public function getDatabases()
	{
		return array_values($this->_dbObjs);
	}
	public function getDatabase($id)
	{
		//if not exist, add
		$dbObj	= $this->getDatabaseById($id, false);
		if ($dbObj === null) {
			$dbObj	= $this->addDatabase($id);
		}
		return $dbObj;
	}
	public function addDatabase($id)
	{
		if ($this->getDatabaseById($id, false) !== null) {
			throw new \Exception("Database already exist: ".$id);
		}
		$dbObj	= new \MTM\RedisApi\Models\Databases\V1($this, $id);
		$this->_dbObjs[$dbObj->getGuid()]	= $dbObj;
		return $dbObj;
	}
	public function removeDatabase($dbObj)
	{
		if (array_key_exists($dbObj->getGuid(), $this->_dbObjs) === true) {
			unset($this->_dbObjs[$dbObj->getGuid()]);
			$dbObj->terminate();
		} else {
			throw new \Exception("Database does not belong to this client");
		}
	}
	public function getDatabaseById($id, $throw=false)
	{
		foreach ($this->_dbObjs as $dbObj) {
			if ($dbObj->getId() == $id) {
				return $dbObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("Database does not exist: ".$id);
		} else {
			return null;
		}
	}
	public function setDatabase($id)
	{
		if ($this->_dbId !== $id) {
			if (is_int($id) === false) {
				throw new \Exception("Invalid database id");
			}
			
			$this->mainSocketWrite($this->getRawCmd("SELECT", array($id)));
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
	public function mainSocketWrite($strCmd)
	{
		return $this->socketWrite($this->getMainSocket(), $strCmd);
	}
	public function chanSocketRead($throw=false, $timeout=5000)
	{
		$sTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		$rData	= $this->socketRead($this->getChanSocket(), $throw, $timeout);
		$eTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		$rTime	= $timeout - (($eTime - $sTime) * 1000);

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
						if (preg_match("/^(__keyevent|__keyspace)\@/", $chanName) === 0) {
							//TODO: investigate if clients can signal the default serializer used to redis
							//so all messages are serialized the same, this is not exactly sustainable
							$payload	= $this->dataDecode($payload);
						}
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
						if (preg_match("/^(__keyevent|__keyspace)\@/", $chanName) === 0) {
							//TODO: investigate if clients can signal the default serializer used to redis
							//so all messages are serialized the same, this is not exactly sustainable
							$payload	= $this->dataDecode($payload);
						}
						$chanObj->addMsg($chanName, $payload);
					}
					
				} else {
					break;
				}
			}
		}
		if ($rData == "" && $rTime > 0) {
			//we got a message, but no real data, 
			//this will not end up nesting too deep, pub / sub messages are read many at a time
			//its only if we trigger a subscribe/unsubscribe and there is a pub pending that we recurse
			return $this->chanSocketRead($throw, $rTime);
		} else {
			return $rData;
		}
	}
	public function chanSocketWrite($strCmd)
	{
		return $this->socketWrite($this->getChanSocket(), $strCmd);
	}
	public function getRawCmd($cmd, $args=array())
	{
		$cmdStr		= "*".(1+count($args))."\r\n\$".strlen($cmd)."\r\n".$cmd."\r\n";
		foreach ($args as $arg) {
			$cmdStr	.= "\$".strlen($arg)."\r\n".$arg."\r\n";
		}
		return $cmdStr;
	}
	public function setDataEncoder($name)
	{
		if (in_array($name, array("php-serializer", "none")) === true) {
			$this->_encoder	= $name;
			return $this;
		} else {
			throw new \Exception("Invalid encoder: ". $name);
		}
	}
	public function dataEncode($data)
	{
		if ($this->_encoder === "none") {
			return $data;
		} elseif ($this->_encoder === "php-serializer") {
			return serialize($data);
		} else {
			throw new \Exception("Invalid encoder: ". $this->_encoder);
		}
	}
	public function dataDecode($data)
	{
		if ($this->_encoder === "none") {
			return $data;
		} elseif ($this->_encoder === "php-serializer") {
			return unserialize($data);
		} else {
			throw new \Exception("Invalid encoder: ". $this->_encoder);
		}
	}
	protected function newSocket()
	{
		//src: https://redis.io/topics/pubsub
		//A client subscribed to one or more channels should not issue commands,
		//although it can subscribe and unsubscribe to and from other channels.
		//TL;DR this means channels must have their own dedicated socket
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
				$this->socketWrite($sockRes, $this->getRawCmd("AUTH", array($this->_authStr)));
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
	public function getPhpRedis()
	{
		//php Redis functionality will be replaced over time
		//right now we just want to have non blocking subscriptions
		if ($this->_phpRedisObj === null) {
			if (extension_loaded("redis") === false) {
				//is the extension added under php.ini? extension=/usr/lib64/php/modules/redis.so
				throw new \Exception("PhpRedis extension not loaded");
			}
			$this->_phpRedisObj		= new \Redis();
			$this->_phpRedisObj->connect($this->_hostname, $this->_portNbr);
			if ($this->_authStr != "") {
				$this->_phpRedisObj->auth($this->_authStr);
			}
		}
		return $this->_phpRedisObj;
	}
	public function terminate($throw=true)
	{
		$errObj	= null;
		foreach ($this->getDatabases() as $dbObj) {
			try {
				$this->removeDatabase($dbObj);
			} catch (\Exception $e) {
				if ($errObj === null) {
					$errObj	= $e;
				}
			}
		}
		foreach ($this->getStreams() as $streamObj) {
			try {
				$this->removeStream($streamObj);
			} catch (\Exception $e) {
				if ($errObj === null) {
					$errObj	= $e;
				}
			}
		}
		if ($this->_chSockObj !== null) {
			//clear the socket, dont convert the data into messages,
			//takes forever if lots of messages have been published ignoring dubs
			$this->socketRead($this->_chSockObj, false, 1);
			foreach ($this->getChannels() as $chanObj) {
				try {
					$this->removeChannel($chanObj);
				} catch (\Exception $e) {
					if ($errObj === null) {
						$errObj	= $e;
					}
				}
			}
			try {
				$this->socketWrite($this->_chSockObj, $this->getRawCmd("QUIT"));
				$rData		= $this->chanSocketRead(true);
				if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
					fclose($this->_chSockObj);
					$this->_chSockObj	= null;
				} elseif (strpos($rData, "-ERR") === 0) {
					throw new \Exception("Error: ".$rData);
				} else {
					throw new \Exception("Not handled for return: ".$rData);
				}
			} catch (\Exception $e) {
				if ($errObj === null) {
					$errObj	= $e;
				}
			}
		}
		if ($this->_mainSockObj !== null) {
			try {
				$this->socketRead($this->_mainSockObj, false, 1); //clear the socket
				$this->socketWrite($this->_mainSockObj, $this->getRawCmd("QUIT"));
				$rData		= $this->mainSocketRead(true);
				if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
					fclose($this->_mainSockObj);
					$this->_mainSockObj	= null;
				} elseif (strpos($rData, "-ERR") === 0) {
					throw new \Exception("Error: ".$rData);
				} else {
					throw new \Exception("Not handled for return: ".$rData);
				}
			} catch (\Exception $e) {
				if ($errObj === null) {
					$errObj	= $e;
				}
			}
		}
		if ($errObj === null) {
			return $this;
		} elseif ($throw === true) {
			throw $errObj;
		} else {
			return $errObj;
		}
	}
}