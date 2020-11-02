<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\SetDx;

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
		$data	= str_replace(array("'"), array("\'"), $this->getClient()->dataEncode($this->getValue()));
		$strCmd	= "redis.call('select',".$this->getDb()->getId().")";
		$strCmd	.= " if redis.call('exists','".$this->getString()->getKey()."') == 0 then return 'SETDX-FAIL'";
		$strCmd	.= " elseif redis.call('type','".$this->getString()->getKey()."').ok ~= 'string' then return 'SETDX-INVALID'";
		$strCmd	.= " else redis.call('set', '".$this->getString()->getKey()."', '".$data."') return 'SETDX-OK'";
		$strCmd	.= " end";
		return $this->getClient()->getRawCmd("EVAL", array($strCmd, 1, 1));
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->parse($this->getClient()->newEval($this->getRawCmd())->exec($throw));
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
		$nPos	= strpos($rData, "\r\n");
		$cLen	= intval(substr($rData, 1, ($nPos-1)));
		$rData	= substr($rData, ($nPos+2), $cLen);
		if ($rData == "SETDX-OK") {
			$this->setResponse(true);
		} elseif ($rData == "SETDX-FAIL") {
			$this->setResponse(null)->setException(new \Exception("Key does not exist: ".$this->getString()->getKey()));
		} elseif ($rData == "SETDX-INVALID") {
			$this->setResponse(null)->setException(new \Exception("Key is not type string: ".$this->getString()->getKey()));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}