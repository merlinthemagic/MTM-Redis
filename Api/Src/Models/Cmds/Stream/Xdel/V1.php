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
		if (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		}
		$count	= $this->getClient()->parseResponse($rData);
		$this->setResponse($count);
		if ($count === 0) {
			$this->setException(new \Exception("Id did not exist: ".$this->getId()));
		}
		return $this;
	}
}