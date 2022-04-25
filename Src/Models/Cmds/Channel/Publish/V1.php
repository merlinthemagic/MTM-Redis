<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Channel\Publish;

class V1 extends Base
{
	protected $_msg=null;
	
	public function setMessage($msg)
	{
		$this->_msg		= $msg;
		return $this;
	}
	public function getMessage()
	{
		return $this->_msg;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getChannel()->getName(), $this->getMessage()));
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->parse($this->getSocket()->write($this->getRawCmd())->read(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		$rVal	= $this->getClient()->parseResponse($rData);
		if (is_int($rVal) === true) {
			$this->setResponse($rVal);
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