<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class ClientId extends Base
{
	protected $_baseCmd="CLIENT";

	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array("ID"));
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
		if (preg_match("/^\:([0-9]+)\r\n$/si", $rData, $raw) === 1) {
			$this->setResponse(intval($raw[1]));
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}