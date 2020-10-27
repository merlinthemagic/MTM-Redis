<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Multi;

class V1 extends Base
{
	protected $_cmdObjs=array();
	
	public function addCmd($cmdObj)
	{
		if ($this->isExec() === false) {
			$this->_cmdObjs[]		= $cmdObj;
			return $this;
		} else {
			throw new \Exception("Cannot add command, transaction is complete");
		}
	}
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array());
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {

			$this->parse($this->getSocket()->write($this->getRawCmd())->read(true));
			
			try {
				
				foreach ($this->_cmdObjs as $cmdObj) {
					if ($cmdObj->isExec() === true) {
						throw new \Exception("Command already executed: ".$cmdObj->getBaseCmd());
					} elseif ($cmdObj->isQueued() === true) {
						throw new \Exception("Command already queued: ".$cmdObj->getBaseCmd());
					}
					
					$cmdObj->exec(true);
					
					if ($cmdObj->isExec() === false) {
						throw new \Exception("Command failed to execute: ".$cmdObj->getBaseCmd());
					} elseif ($cmdObj->isQueued() === false) {
						throw new \Exception("Command failed to queue: ".$cmdObj->getBaseCmd());
					}
				}
				$this->_isExec	= true;
				$rObj			= $this->getClient()->newExec()->exec(true);
				if ($rObj->count === count($this->_cmdObjs)) {
					foreach ($this->_cmdObjs as $cmdObj) {
						$data	= array_shift($rObj->returns);
						$cmdObj->parse($data);
					}
				} else {
					throw new \Exception("Queue length: ".count($this->_cmdObjs)." does not match return data count: ".$qLen);
				}
				
				//replace the response with the populated commands
				$this->setResponse($this->_cmdObjs);
				$this->_cmdObjs	= null;
				
			} catch (\Exception $e) {
				if ($this->_isExec === false) {
					$this->getClient()->newDiscard()->exec(false);
					$this->_isExec	= true;
				}
				$this->setException($e);
			}
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (preg_match("/^\+(OK)\r\n$/si", $rData, $raw) === 1) {
			$this->setResponse($raw[1]);
		} elseif (strpos($rData, "-ERR") === 0) {
			$this->setException(new \Exception("Error: ".$rData));
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}