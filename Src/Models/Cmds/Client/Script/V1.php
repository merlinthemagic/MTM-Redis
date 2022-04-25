<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Script;

class V1 extends Base
{
	protected $_exp=null;
	
	public function setExp($str)
	{
		//must start with SCRIPT
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
		$rVal	= $this->getClient()->parseResponse($rData);
		if ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} else {
			$this->setResponse($rVal);
		}
		return $this;
	}
}