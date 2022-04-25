<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Channel\Subscribe;

class V1 extends Base
{
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getChannel()->getName()));
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
		if ($arr instanceof \Exception) {
			$this->setException($arr);
		} elseif ($arr[0] != "subscribe") {
			$this->setException(new \Exception("Not handled for return: ".$arr[0]));
		} elseif ($arr[1] != $this->getChannel()->getName()) {
			$this->setException(new \Exception("Not handled for return: ".$arr[1]));
		} elseif (is_int($arr[2]) === false) {
			$this->setException(new \Exception("Not handled for return: ".$arr[2]));
		} else {
			$this->setResponse($arr[2]);
		}
		return $this;
	}
}