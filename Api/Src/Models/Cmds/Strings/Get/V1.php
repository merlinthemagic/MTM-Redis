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
		$rVal	= $this->getClient()->parseResponse($rData);
		if ($rVal === "QUEUED") {
			$this->_isQueued	= true;
		} elseif ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} elseif ($rVal === false) {
			$this->setException(new \Exception("Key does not exist: ".$this->getString()->getKey(), 7554)); //code used by key tracking
		} else {
			$data	= $this->getClient()->dataDecode($rVal);
			$this->setResponse($data);
		}
		return $this;
	}
}