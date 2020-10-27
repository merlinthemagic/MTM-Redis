<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

class Zstance extends Sockets
{
	protected $_phpRedisObj=null;
	
	public function getRawCmd($cmd, $args=array())
	{
		$cmdStr		= "*".(1+count($args))."\r\n\$".strlen($cmd)."\r\n".$cmd."\r\n";
		foreach ($args as $arg) {
			$cmdStr	.= "\$".strlen($arg)."\r\n".$arg."\r\n";
		}
		return $cmdStr;
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
		foreach ($this->getChannels() as $chanObj) {
			try {
				$this->removeChannel($chanObj);
			} catch (\Exception $e) {
				if ($errObj === null) {
					$errObj	= $e;
				}
			}
		}
		$this->_mainSockObj	= null;
		$this->_subSockObj	= null;
		
		if ($errObj === null) {
			return $this;
		} elseif ($throw === true) {
			throw $errObj;
		} else {
			return $errObj;
		}
	}
}