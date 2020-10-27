<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Evaluate;

class V1 extends Base
{
	protected $_exp=null;
	
	public function setExp($str)
	{
		$this->_exp	= $str;
		return $this;
	}
	public function getExp()
	{
		return $this->_exp;
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->parse($this->getSocket()->write($this->getExp())->read(true));
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (strpos($rData, "-ERR") === 0) {
			$this->setResponse($rData)->setException(new \Exception("Error: ".$rData));
			return $this;
		} else {
			$this->setResponse($rData);
		}
		return $this;
	}
}