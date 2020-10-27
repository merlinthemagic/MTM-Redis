<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Auth;

class V1 extends Base
{
	protected $_str=null;
	
	public function setAuth($str)
	{
		$this->_str		= $str;
		return $this;
	}
	public function getAuth()
	{
		return $this->_str;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getAuth()));
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
		if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
			$this->setResponse(true);
		} elseif (strpos($rData, "-WRONGPASS") === 0) {
			throw new \Exception("Invalid password: ".$rData);
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
			throw new \Exception("Error: ".$rData);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}