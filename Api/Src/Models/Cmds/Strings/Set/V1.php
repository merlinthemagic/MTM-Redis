<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\Set;

class V1 extends Base
{
	protected $_value=null;
	
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
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getString()->getKey(), $data));
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
		if (preg_match("/^\+(OK)\r\n$/si", $rData, $raw) === 1) {
			$this->setResponse($raw[1]);
		} elseif (preg_match("/(^\+QUEUED\r\n)$/si", $rData) === 1) {
			$this->_isQueued	= true;
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(null)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}