<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\Del;

class V1 extends Base
{
	protected $_key=null;
	
	public function setKey($key)
	{
		$this->_key		= $key;
		return $this;
	}
	public function getKey()
	{
		return $this->_key;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getKey()));
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
		if (is_int($rVal) === true) {
			if ($rVal === 1) {
				$this->setResponse($rVal);
			} elseif ($rVal === 0) {
				$this->setResponse($rVal)->setException(new \Exception("Cannot delete, key does not exist"));
			} else {
				throw new \Exception("Not handled for return: ".$rData);
			}
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