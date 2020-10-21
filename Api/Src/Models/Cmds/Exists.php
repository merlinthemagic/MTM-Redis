<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Exists extends Base
{
	protected $_baseCmd="EXISTS";
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
		if (preg_match("/^(\:1\r\n)$/si", $rData) === 1) {
			$this->setResponse(true);
		} elseif (preg_match("/(^\:0\r\n)$/si", $rData) === 1) {
			$this->setResponse(false);
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}