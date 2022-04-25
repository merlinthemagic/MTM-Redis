<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream\Xlen;

class V1 extends Base
{
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getStream()->getKey()));
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
		if ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} elseif (is_int($rVal) === true) {
			$this->setResponse($rVal);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}