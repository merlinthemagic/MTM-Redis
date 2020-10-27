<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\SetNx;

class V1 extends Base
{
	protected $_key=null;
	protected $_value=null;
	
	public function setKey($key)
	{
		$this->_key		= $key;
		return $this;
	}
	public function getKey()
	{
		return $this->_key;
	}
	public function setValue($value)
	{
		$this->_value		= $value;
		return $this;
	}
	public function getValue()
	{
		return $this->_value;
	}
	public function getRawCmd()
	{
		$data	= $this->getClient()->dataEncode($this->getValue());
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getKey(), $data));
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
			$this->setResponse(intval($raw[1]))->setException(new \Exception("Key exists: ".$this->getKey()));
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}