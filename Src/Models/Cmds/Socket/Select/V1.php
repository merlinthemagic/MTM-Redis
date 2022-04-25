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