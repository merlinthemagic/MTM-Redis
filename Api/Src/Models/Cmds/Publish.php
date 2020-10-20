<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Publish extends Base
{
	protected $_baseCmd="PUBLISH";
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
		$data	= $this->getClient()->dataEncode($this->getMessage());
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getParent()->getName(), $data));
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->parse($this->getClient()->mainSocketWrite($this->getRawCmd())->mainSocketRead(true));
			if (
				$this->getException() === null 
				&& $this->getParent()->isSubscribed() === true
			) {
				$this->getParent()->addIgnoreOncePayload($this->getMessage());
			}
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (preg_match("/^\:([0-9]+)\r\n$/si", $rData, $raw) === 1) {
			$this->setResponse(intval($raw[1]));
		} elseif (preg_match("/(^\+QUEUED\r\n)$/si", $rData) === 1) {
			$this->_isQueued	= true;
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}