<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

//SET if Does eXist

class SetDx extends Base
{
	protected $_baseCmd="EVAL";
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
		$data	= str_replace(array("'"), array("\'"), $this->getClient()->dataEncode($this->getValue()));
		$strCmd	= "if redis.call('exists','".$this->getKey()."') == 1 then redis.call('set', '".$this->getKey()."', '".$data."') return 'SETDX-OK' else return 'SETDX-FAIL' end";
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($strCmd, 1, 1));
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->getClient()->setDatabase($this->getParent()->getId());
			$this->parse($this->getClient()->getMainSocket()->write($this->getRawCmd())->read(true));
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
		if ($cLen !== 8 && $cLen !== 10) {
			$this->setResponse(false)->setException(new \Exception("SetDx returned incorrect return length: ".$cLen));
			return $this;
		}
		$rData	= substr($rData, ($nPos+2), $cLen);
		if ($rData == "SETDX-OK") {
			$this->setResponse(true);
		} elseif ($rData == "SETDX-FAIL") {
			$this->setResponse(false)->setException(new \Exception("Key does not exist: ".$this->getKey()));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}