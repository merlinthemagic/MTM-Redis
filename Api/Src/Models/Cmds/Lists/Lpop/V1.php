<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Lists\Lpop;

class V1 extends Base
{
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array($this->getList()->getKey()));
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->selectDb()->parse($this->getSocket()->write($this->getRawCmd())->read(true));
			$this->getDb()->trackingPostCmd($this->getList());
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
		if ($cLen < 0) {
			$this->setResponse(null)->setException(new \Exception("List is empty"));
			return $this;
		} else {
			$data	= $this->getClient()->dataDecode(substr($rData, ($nPos+2), $cLen));
			$this->setResponse($data);
		}
		return $this;
	}
}