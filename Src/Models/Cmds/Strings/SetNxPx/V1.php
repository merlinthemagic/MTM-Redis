<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\SetNxPx;

class V1 extends Base
{
	protected $_value=null;
	protected $_expire=null;
	
	public function setValue($value)
	{
		$this->_value		= $value;
		return $this;
	}
	public function getValue()
	{
		return $this->_value;
	}
	public function setExpire($value)
	{
		if (is_int($value) === false) {
			throw new \Exception("Input must be integer");
		}
		$this->_expire		= $value;
		return $this;
	}
	public function getExpire()
	{
		return $this->_expire;
	}
	public function getRawCmd()
	{
		$data	= $this->getClient()->dataEncode($this->getValue());
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getString()->getKey(), $data, "NX", "PX", $this->getExpire()));
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->selectDb()->parse($this->getSocket()->write($this->getRawCmd())->read(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		$rVal	= $this->getClient()->parseResponse($rData);
		if ($rVal === "OK") {
			$this->setResponse($rVal);
		} elseif ($rVal === false) {
			$this->setException(new \Exception("Key exists, cannot create", 8865));//code use in string to get lock
		} elseif ($rVal === "QUEUED") {
			$this->_isQueued	= true;
		} elseif ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}