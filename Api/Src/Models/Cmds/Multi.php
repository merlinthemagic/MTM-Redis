<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class Multi extends Base
{
	protected $_baseCmd="MULTI";
	protected $_cmdObjs=array();
	
	public function addCmd($cmdObj)
	{
		if ($this->_isExec === false) {
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
		if ($this->_isExec === false) {
			$this->getClient()->setDatabase($this->getParent()->getId());
			$this->parse($this->getClient()->getMainSocket()->write($this->getRawCmd())->read(true));
			
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

				$rData			= $this->getClient()->getMainSocket()->write($this->getClient()->getRawCmd("EXEC", array()))->read(true);
				$this->_isExec	= true;
				$nPos			= strpos($rData, "\r\n");
				$qLen			= intval(substr($rData, 1, $nPos));
				if ($qLen < 0) {
					//maybe a watched key was changed
					throw new \Exception("Transaction failed");
					
				} elseif ($qLen === count($this->_cmdObjs)) {
					$rData	= substr($rData, ($nPos+2));
					foreach ($this->_cmdObjs as $cmdObj) {
						
						$nPos	= strpos($rData, "\r\n");
						if (strpos($rData, "\$") === 0) {
							$dLen	= intval(substr($rData, 1, $nPos));
							if ($dLen < 0) {
								$ePos	= 5;
							} else {
								$ePos	= ($nPos+$dLen+4);
							}
						} else {
							$ePos	= ($nPos+2);
						}

						$data		= substr($rData, 0, $ePos);
						$rData		= substr($rData, $ePos);
						$cmdObj->parse($data);
					}
					if ($rData != "") {
						throw new \Exception("We have data left over after completing the transaction: ".$rData);
					}

				} else {
					throw new \Exception("Queue length: ".count($this->_cmdObjs)." does not match return data count: ".$qLen);
				}
				
				//replace the response with the populated commands
				$this->setResponse($this->_cmdObjs);
				$this->_cmdObjs	= null;

			} catch (\Exception $e) {
				if ($this->_isExec === false) {
					$this->discard();
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
	protected function discard()
	{
		$this->parse($this->getClient()->mainSocketWrite($this->getClient()->getRawCmd("DISCARD", array()))->mainSocketRead(true));
		return $this;
	}
}