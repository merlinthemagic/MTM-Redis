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
		$rVal	= $this->getClient()->parseResponse($rData);
		if ($rVal === "OK") {
			$this->setResponse($rVal);
		} elseif ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}