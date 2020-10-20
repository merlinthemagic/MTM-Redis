<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Punsubscribe extends Base
{
	protected $_baseCmd="PUNSUBSCRIBE";
	protected $_pattern=null;
	
	public function setPattern($pattern)
	{
		$this->_pattern		= $pattern;
		return $this;
	}
	public function getPattern()
	{
		return $this->_pattern;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getPattern()));
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->parse($this->getClient()->chanSocketWrite($this->getRawCmd())->chanSocketRead(true));
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
			$this->setResponse(false)->setException(new \Exception("Punsubscribe returned incorrect parameter count: ".$cLen));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$cLen		= intval(substr($rData, 1, ($nPos-1)));
		if ($cLen !== 12) {
			$this->setResponse(false)->setException(new \Exception("Punsubscribe returned incorrect command length: ".$cLen));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$strCmd		= substr($rData, 0, $nPos);
		if ($strCmd !== "punsubscribe") {
			$this->setResponse(false)->setException(new \Exception("Punsubscribe returned incorrect command: ".$strCmd));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$cLen		= intval(substr($rData, 1, ($nPos-1)));
		if ($cLen !== strlen($this->getPattern())) {
			$this->setResponse(false)->setException(new \Exception("Punsubscribe returned incorrect pattern length: ".$cLen));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$pattern	= substr($rData, 0, $nPos);
		if ($pattern !== $this->getPattern()) {
			$this->setResponse(false)->setException(new \Exception("Punsubscribe returned incorrect pattern: ".$pattern));
			return $this;
		}
		$rData		= substr($rData, ($nPos+2));
		$nPos		= strpos($rData, "\r\n");
		$cLen		= substr($rData, 1, ($nPos-1));
		$rData		= substr($rData, ($nPos+2));
		if ($rData != "") {
			$this->setResponse(false)->setException(new \Exception("Punsubscribe returned extra data: ".$rData));
			return $this;
		} elseif (ctype_digit($cLen) === true) {
			//number of total subscribers
			$this->setResponse(intval($cLen));
			return $this;
		}
	}
}