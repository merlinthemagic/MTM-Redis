<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

class WatchMulti extends Base
{
	protected $_watchObjs=array();
	protected $_cmdObjs=array();
	
	public function addWatch($cmdObj)
	{
		if ($this->_isExec === false) {
			$this->_watchObjs[]		= $cmdObj;
			return $this;
		} else {
			throw new \Exception("Cannot add watch, transaction is complete");
		}
	}
	public function addCmd($cmdObj)
	{
		if ($this->_isExec === false) {
			$this->_cmdObjs[]		= $cmdObj;
			return $this;
		} else {
			throw new \Exception("Cannot add command, transaction is complete");
		}
	}
	public function exec($throw=false, $timeout=5000)
	{
		if ($this->_isExec === false) {
			
			$dbObj	= $this->getParent();
			$tTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + ($timeout / 1000);
			while (true) {

				try {
					
					foreach ($this->_watchObjs as $cmdObj) {
						//TODO: need to refresh/duplicate the commands
						//they currently will not trigger again
						$cmdObj->exec(true);
					}
					$trsObj			= $dbObj->newTransaction();
					foreach ($this->_cmdObjs as $cmdObj) {
						//TODO: need to refresh/duplicate the commands
						//they currently will not trigger again
						$trsObj->addCmd($cmdObj);
					}
					
					$trsObj->exec(true);
					$dbObj->unwatch()->exec(true);
					break;

				} catch (\Exception $e) {
					$dbObj->unwatch()->exec(false);
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