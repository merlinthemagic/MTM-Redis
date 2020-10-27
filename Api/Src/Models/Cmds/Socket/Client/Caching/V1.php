<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Client\Caching;

class V1 extends Base
{
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
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getClientCmd(), $cache));
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
		if (preg_match("/^\+(OK)\r\n$/si", $rData, $raw) === 1) {
			$this->setResponse($raw[1]);
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}