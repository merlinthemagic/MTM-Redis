<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Quit extends Base
{
	protected $_baseCmd="QUIT";

	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array());
	}
	public function exec($throw=false)
	{
		if ($this->_isExec === false) {
			$this->parse($this->getParent()->write($this->getRawCmd())->read(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (preg_match("/(^\+OK\r\n)$/si", $rData) === 1) {
			$this->setResponse(true);
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
	}
}