<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Flush;

class V1 extends Base
{
	protected $_async=false;
	
	public function setAsync($bool)
	{
		if (is_bool($bool) === false) {
			throw new \Exception("Invalid Input");
		}
		$this->_async		= $bool;
		return $this;
	}
	public function getAsync()
	{
		return $this->_async;
	}
	public function getRawCmd()
	{
		$args	= array();
		if ($this->getAsync() === true) {
			$args[]	= "ASYNC";
		}
		return $this->getClient()->getRawCmd($this->getBaseCmd(), $args);
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