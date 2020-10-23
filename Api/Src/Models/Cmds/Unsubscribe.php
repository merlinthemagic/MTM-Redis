<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Unsubscribe extends Base
{
	protected $_baseCmd="UNSUBSCRIBE";
	protected $_name=null;
	
	public function setName($name)
	{
		$this->_name		= $name;
		return $this;
	}
	public function getName()
	{
		return $this->_name;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getName()));
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->getClient()->getChanSocket()->write($this->getRawCmd());
			$this->parse($this->getClient()->chanSocketRead(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
			return $this;
		}
		$nPos	= strpos($rData, "\r\n");
		$cLen	= intval(substr($rData, 1, ($nPos-1)));
		if ($cLen !== 3) {
			$this->setResponse(false)->setException(new \Exception("Unsubscribe returned incorrect parameter count: ".$cLen));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$cLen		= intval(substr($rData, 1, ($nPos-1)));
		if ($cLen !== 11) {
			$this->setResponse(false)->setException(new \Exception("Unsubscribe returned incorrect command length: ".$cLen));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$strCmd		= substr($rData, 0, $nPos);
		if ($strCmd !== "unsubscribe") {
			$this->setResponse(false)->setException(new \Exception("Unsubscribe returned incorrect command: ".$strCmd));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$cLen		= intval(substr($rData, 1, ($nPos-1)));
		if ($cLen !== strlen($this->getName())) {
			$this->setResponse(false)->setException(new \Exception("Unsubscribe returned incorrect name length: ".$cLen));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$name		= substr($rData, 0, $nPos);
		if ($name !== $this->getName()) {
			$this->setResponse(false)->setException(new \Exception("Unsubscribe returned incorrect name: ".$name));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$cLen		= substr($rData, 1, ($nPos-1));
		$rData		= substr($rData, ($nPos+2));
		if ($rData != "") {
			$this->setResponse(false)->setException(new \Exception("Unsubscribe returned extra data: ".$rData));
			return $this;
		} elseif (ctype_digit($cLen) === true) {
			//number of total subscribers
			$this->setResponse(intval($cLen));
			return $this;
		}
	}
}