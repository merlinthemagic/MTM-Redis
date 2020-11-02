<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Select;

class V1 extends Base
{
	protected $_id=0;
	
	public function setId($int)
	{
		if (is_int($int) === false) {
			throw new \Exception("Invalid input");
		}
		$this->_id		= $int;
		return $this;
	}
	public function getId()
	{
		return $this->_id;
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getId()));
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
		if (preg_match("/^\+(OK)\r\n$/si", $rData, $raw) === 1) {
			$this->setResponse($raw[1]);
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}