<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\Get;

class V1 extends Base
{
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getString()->getKey()));
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->getDb()->trackingPreCmd($this->getString());
			$this->selectDb()->parse($this->getSocket()->write($this->getRawCmd())->read(true));
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
			$this->setException(new \Exception("Key does not exist: ".$this->getString()->getKey(), 7554)); //code used by key tracking
		} elseif (strpos($rData, "-ERR") === 0 || strpos($rData, "-WRONGTYPE") === 0) {
			$this->setResponse(null)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}