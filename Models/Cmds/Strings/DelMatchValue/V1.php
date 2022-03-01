<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\DelMatchValue;

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
		$cObj	= $this->getClient();
		$hash	= "901896440e353affb65b66db5a17e51a95d3173c"; //you must recalculate this if you change the script
		if ($cObj->isScriptLoaded($hash) === false) {
			//load the script, use parameterized scripts to avoid cache growing, this because the script hashes to the same every time
			$strCmd	= "redis.call('select',ARGV[1])";
			$strCmd	.= " if redis.call('exists',KEYS[1]) == 0 then return 'KEY-NOT-EXIST'";
			$strCmd	.= " elseif redis.call('type',KEYS[1]).ok ~= 'string' then return 'KEY-NOT-STRING'";
			$strCmd	.= " elseif redis.call('get',KEYS[1]) ~= ARGV[2] then return 'VALUE-NOT-MATCH'";
			$strCmd	.= " else redis.call('del', KEYS[1]) return 'DELETED-OK'";
			$strCmd	.= " end";
			$cObj->loadScript($strCmd);
		}
		$data	= str_replace(array("'"), array("\'"), $cObj->dataEncode($this->getValue()));
		return $cObj->getRawCmd("EVALSHA", array($hash, 1, $this->getString()->getKey(), $this->getDb()->getId(), $data));
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
		//this is EVAL response, already run through the parser
		if ($rData == "DELETED-OK") {
			$this->setResponse(true);
		} elseif ($rData == "KEY-NOT-EXIST") {
			$this->setException(new \Exception("Key does not exist: ".$this->getString()->getKey()));
		} elseif ($rData == "KEY-NOT-STRING") {
			$this->setException(new \Exception("Key is not type string"));
		} elseif ($rData == "VALUE-NOT-MATCH") {
			$this->setException(new \Exception("Value does not match"));
		} elseif ($rData instanceof \Exception) {
			$this->setException($rData);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}