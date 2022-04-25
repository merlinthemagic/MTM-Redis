<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream\Xdel;

class V1 extends Base
{
	protected $_id=null;
	
	public function setId($id)
	{
		$this->_id		= $id;
		return $this;
	}
	public function getId()
	{
		return $this->_id;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getStream()->getKey(), $this->getId()));
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
		if ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} elseif (is_int($rVal) === true && $rVal === 0) {
			$this->setException(new \Exception("Id did not exist: ".$this->getId()));
		} else {
			$this->setResponse($rVal);
		}
		return $this;
	}
}