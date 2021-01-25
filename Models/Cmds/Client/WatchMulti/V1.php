<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\WatchMulti;

class V1 extends Base
{
	protected $_watchObjs=array();
	protected $_cmdObjs=array();
	
	public function addWatch($cmdObj)
	{
		if ($this->isExec() === false) {
			$this->_watchObjs[]		= $cmdObj;
			return $this;
		} else {
			throw new \Exception("Cannot add watch, transaction is complete");
		}
	}
	public function addCmd($cmdObj)
	{
		if ($this->isExec() === false) {
			$this->_cmdObjs[]		= $cmdObj;
			return $this;
		} else {
			throw new \Exception("Cannot add command, transaction is complete");
		}
	}
	public function exec($throw=false, $timeout=10000)
	{
		if ($this->isExec() === false) {
			
			$tTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + ($timeout / 1000);
			while (true) {
				
				try {
					
					foreach ($this->_watchObjs as $watchObj) {
						$watchObj->reset()->exec(true);
					}
					foreach ($this->_cmdObjs as $cmdObj) {
						$cmdObj->reset();
					}
					$trsObj			= $this->getClient()->newMulti($this->_cmdObjs);
					$trsObj->exec(true);
					$this->getClient()->newUnwatch()->exec(true);
					break;
					
				} catch (\Exception $e) {
					$this->getClient()->newUnwatch()->exec(false);
				}
				
				if (\MTM\Utilities\Factories::getTime()->getMicroEpoch() > $tTime) {
					$this->setException(new \Exception("Transaction timeout"));
					break;
				}
			}
			
			$this->setResponse($this->_cmdObjs);
			$this->_cmdObjs		= null;
			$this->_watchObjs	= null;
			$this->_isExec		= true;
		}
		return $this->getResponse($throw);
	}
}