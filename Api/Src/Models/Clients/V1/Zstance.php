<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

class Zstance extends Sockets
{
	protected $_pData="";
	
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
			return @unserialize($data);
		} else {
			throw new \Exception("Invalid encoder: ". $this->_encoder);
		}
	}
	protected function parseArray()
	{
		$reData			= array();
		$nPos			= strpos($this->_pData, "\r\n");
		$reSize			= intval(substr($this->_pData, 1, ($nPos-1)));
		$this->_pData	= substr($this->_pData, ($nPos+2));
		for ($x=0; $x<$reSize; $x++) {
			$reData[]	= $this->parser();
		}
		return $reData;
	}
	protected function parseString()
	{
		$nPos			= strpos($this->_pData, "\r\n");
		$reSize			= intval(substr($this->_pData, 1, ($nPos-1)));
		if ($reSize < 0) {
			$reData			= false;
			$this->_pData	= substr($this->_pData, ($nPos+2));
		} else {
			$reData			= substr($this->_pData, ($nPos+2), $reSize);
			$this->_pData	= substr($this->_pData, ($nPos+$reSize+4));
		}
		return $reData;
	}
	protected function parseInteger()
	{
		$nPos			= strpos($this->_pData, "\r\n");
		$reData			= intval(substr($this->_pData, 1, ($nPos-1)));
		$this->_pData	= substr($this->_pData, ($nPos+2));
		return $reData;
	}
	protected function parseAck()
	{
		$nPos			= strpos($this->_pData, "\r\n");
		$reData			= substr($this->_pData, 1, ($nPos-1));
		$this->_pData	= substr($this->_pData, ($nPos+2));
		return $reData;
	}
	protected function parseError()
	{
		$nPos			= strpos($this->_pData, "\r\n");
		$reData			= new \Exception("Error: ".substr($this->_pData, 4, ($nPos-4)));
		$this->_pData	= substr($this->_pData, ($nPos+2));
		return $reData;
	}
	protected function parseErrorType()
	{
		$nPos			= strpos($this->_pData, "\r\n");
		$reData			= new \Exception("Wrong Type: ".substr($this->_pData, 10, ($nPos-10)));
		$this->_pData	= substr($this->_pData, ($nPos+2));
		return $reData;
	}
	protected function parseErrorAuth()
	{
		$nPos			= strpos($this->_pData, "\r\n");
		$reData			= new \Exception("Wrong Password: ".substr($this->_pData, 10, ($nPos-10)));
		$this->_pData	= substr($this->_pData, ($nPos+2));
		return $reData;
	}
	protected function parser()
	{
		if (strpos($this->_pData, "*") === 0) {
			return $this->parseArray();
		} elseif (strpos($this->_pData, "\$") === 0) {
			return $this->parseString();
		} elseif (strpos($this->_pData, ":") === 0) {
			return $this->parseInteger();
		} elseif (strpos($this->_pData, "+") === 0) {
			return $this->parseAck();
		} elseif (strpos($this->_pData, "-ERR") === 0) {
			return $this->parseError();
		} elseif (strpos($this->_pData, "-WRONGTYPE") === 0) {
			return $this->parseErrorType();
		} elseif (strpos($this->_pData, "-WRONGPASS") === 0) {
			return $this->parseErrorAuth();
		} else {
			throw new \Exception("Not handled for response data: ".$this->_pData);
		}
	}
	public function parseResponse($rData)
	{
		$this->_pData	= $rData;
		return $this->parser();
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