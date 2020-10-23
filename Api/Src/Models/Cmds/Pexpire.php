<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Pexpire extends Base
{
	protected $_baseCmd="PEXPIRE";
	protected $_key=null;
	protected $_ms=null;
	
	public function setKey($key)
	{
		$this->_key		= $key;
		return $this;
	}
	public function getKey()
	{
		return $this->_key;
	}
	public function setExpire($value)
	{
		$this->_ms		= $value;
		return $this;
	}
	public function getExpire()
	{
		return $this->_ms;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getKey(), $this->getExpire()));
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->getClient()->setDatabase($this->getParent()->getId());
			$this->parse($this->getClient()->getMainSocket()->write($this->getRawCmd())->read(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (preg_match("/^(\:1\r\n)$/si", $rData) === 1) {
			$this->setResponse(true);
		} elseif (preg_match("/(^\:0\r\n)$/si", $rData) === 1) {
			$this->setResponse(false)->setException(new \Exception("Key does not exist: ".$this->getKey()));
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}