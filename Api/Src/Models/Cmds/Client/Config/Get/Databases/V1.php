<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Config\Get\Databases;

class V1 extends Base
{
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getConfCmd(), $this->getSubCmd()));
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
		$arr	= $this->getClient()->parseResponse($rData);
		if ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} elseif ($arr[0] != "databases") {
			$this->setException(new \Exception("Not handled for return: ".$arr[0]));
		} elseif (ctype_digit($arr[1]) !== true) {
			$this->setException(new \Exception("Not handled for return: ".$arr[1]));
		} else {
			$this->setResponse(intval($arr[1]));
		}
		return $this;
	}
}