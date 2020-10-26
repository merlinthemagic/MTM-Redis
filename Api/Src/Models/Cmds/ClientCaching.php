<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class ClientCaching extends Base
{
	protected $_baseCmd="CLIENT";
	protected $_cache=true;
	
	public function setCache($bool)
	{
		if (is_bool($bool) === false) {
			throw new \Exception("Invalid input");
		}
		$this->_cache		= $bool;
		return $this;
	}
	public function getCache()
	{
		return $this->_cache;
	}
	public function getRawCmd()
	{
		$cache	= "YES";
		if ($this->getCache() === false) {
			$cache	= "NO";
		}
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array("CACHING", $cache));
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->parse($this->getParent()->write($this->getRawCmd())->read(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
			$this->setResponse(intval($raw[1]));
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}