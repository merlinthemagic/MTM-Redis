<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases;

class V1 extends Base
{
	public function getValueByKey($key, $throw=false)
	{
		$this->getParent()->setDatabase($this->getId());
		
		$cmdStr		= $this->getParent()->getRawCmd("GET", array($key));
		$this->getParent()->mainSocketWrite($cmdStr);

		$rData		= $this->getParent()->mainSocketRead(true);
		$nPos		= strpos($rData, "\r\n");
		$dLen		= intval(substr($rData, 1, $nPos));
		if ($dLen > -1) {
			return substr($rData, ($nPos + 2), $dLen);
		} elseif ($throw === true) {
			throw new \Exception("Key does not exist");
		} else {
			return null;
		}
	}
	public function setValueByKey($key, $value)
	{
		$this->getParent()->setDatabase($this->getId());
		
		$cmdStr		= $this->getParent()->getRawCmd("SET", array($key, $value));
		$this->getParent()->mainSocketWrite($cmdStr);

		$rData		= $this->getParent()->mainSocketRead(true);
		if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
			return $this;
		} elseif (strpos($rData, "-ERR") === 0) {
			throw new \Exception("Error: ".$rData);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
	}
	public function terminate($throw=true)
	{
		$errObj	= null;
		if ($errObj === null) {
			return $this;
		} elseif ($throw === true) {
			throw $errObj;
		} else {
			return $errObj;
		}
		return $this;
	}
}