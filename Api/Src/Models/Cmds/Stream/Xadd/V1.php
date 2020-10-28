<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream\Xadd;

class V1 extends Base
{
	protected $_id="*";
	protected $_fields=array();
	
	public function setId($id)
	{
		$this->_id		= $id;
		return $this;
	}
	public function getId()
	{
		return $this->_id;
	}
	public function addField($field, $value)
	{
		if ($this->isExec() === false) {
			$this->_fields[$field]	= $value;
			return $this;
		} else {
			throw new \Exception("Cannot add field, command is complete");
		}
	}
	public function getRawCmd()
	{
		$args	= array($this->getStream()->getKey(), $this->getId());
		foreach ($this->_fields as $field => $value) {
			$args[]		= $field;
			$args[]		= $this->getClient()->dataEncode($value);
		}
		return $this->getClient()->getRawCmd($this->getBaseCmd(), $args);
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
			return $this;
		}
		$this->setResponse($this->getClient()->parseResponse($rData));
		return $this;
	}
}