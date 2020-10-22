<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Ttl extends Base
{
	protected $_baseCmd="TTL";
	protected $_key=null;
	
	public function setKey($key)
	{
		$this->_key		= $key;
		return $this;
	}
	public function getKey()
	{
		return $this->_key;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getKey()));
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->parse($this->getClient()->mainSocketWrite($this->getRawCmd())->mainSocketRead(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (preg_match("/^(\:([0-9]+)\r\n)$/si", $rData, $raw) === 1) {
			$this->setResponse(intval($raw[1]));
		} elseif (preg_match("/(^\:-2\r\n)$/si", $rData) === 1) {
			$this->setResponse(false)->setException(new \Exception("Key does not exist: ".$this->getKey()));
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}