<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Get extends Base
{
	protected $_baseCmd="GET";
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
			$this->getClient()->setDatabase($this->getParent()->getId());
			$this->parse($this->getClient()->mainSocketWrite($this->getRawCmd())->mainSocketRead(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (preg_match("/^\\\$([0-9]+)\r\n/si", $rData, $raw, PREG_OFFSET_CAPTURE) === 1) {
			$data	= $this->getClient()->dataDecode(substr($rData, ($raw[1][1]+strlen($raw[1][0])+2), $raw[1][0]));
			$this->setResponse($data);
		} elseif (preg_match("/(^\+QUEUED\r\n)$/si", $rData) === 1) {
			$this->_isQueued	= true;
		} elseif (preg_match("/(^\\\$-1\r\n)$/si", $rData) === 1) {
			$this->setResponse(false)->setException(new \Exception("Key does not exist: ".$this->getKey()));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}