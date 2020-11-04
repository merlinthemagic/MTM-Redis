<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\ExpireAt;

class V1 extends Base
{
	protected $_key=null;
	protected $_epoch=null;
	
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
		$this->_epoch		= $value;
		return $this;
	}
	public function getExpire()
	{
		return $this->_epoch;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getKey(), $this->getExpire()));
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
		if (preg_match("/^\:(1)\r\n$/si", $rData) === 1) {
			$this->setResponse(true);
		} elseif (preg_match("/(^\:0\r\n)$/si", $rData) === 1) {
			$this->setException(new \Exception("Key does not exist: ".$this->getKey()));
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}