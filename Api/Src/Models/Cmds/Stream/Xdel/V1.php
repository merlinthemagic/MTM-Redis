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
		if (preg_match("/^\:(1)\r\n$/si", $rData, $raw) === 1) {
			$this->setResponse(intval($raw[1]));
		} elseif (preg_match("/^\:(0)\r\n$/si", $rData, $raw) === 1) {
			$this->setResponse(intval($raw[1]))->setException(new \Exception("Id did not exist: ".$this->getId()));
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}