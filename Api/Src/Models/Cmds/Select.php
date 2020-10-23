<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Select extends Base
{
	protected $_baseCmd="SELECT";
	protected $_id=null;
	
	public function setId($id)
	{
		if (is_int($id) === false) {
			throw new \Exception("Invalid database id");
		}
		$this->_id		= $id;
		return $this;
	}
	public function getId()
	{
		return $this->_id;
	}
	public function getRawCmd()
	{
		return $this->getParent()->getRawCmd($this->getBaseCmd(), array($this->getId()));
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->parse($this->getParent()->getMainSocket()->write($this->getRawCmd())->read(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
			$this->setResponse(true);
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}