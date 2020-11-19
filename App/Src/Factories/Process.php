<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Factories;

class Process extends Base
{
	protected $_loopCbs=array();
	protected $_lastMsg=0;
	protected $_maxIdle=2;
	protected $_runStatus=false;
	
	public function addLoopCb($obj, $method)
	{
		$this->removeLoopCb($obj, $method);
		$this->_loopCbs[]	= array($obj, $method);
		$this->resetRunStatus();
		return $this;
	}
	public function removeLoopCb($obj, $method)
	{
		foreach ($this->_loopCbs as $e => $eObj) {
			if ($eObj[0] === $obj && $eObj[1] === $method) {
				unset($this->_loopCbs[$e]);
				$this->resetRunStatus();
				break;
			}
		}
		return $this;
	}
	public function runLoop($runTime=-1)
	{
		if ($this->_runStatus === false) {
			throw new \Exception("Need at least one client or a call back");
		}
		if ($runTime < 0) {
			while($this->_runStatus === true) {
				$this->runOnce();
			}
		} else {
			$cTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
			$tTime	= $cTime + $runTime;
			while($this->_runStatus === true && $tTime > $cTime) {
				$this->runOnce();
				$cTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
			}
		}
	}
	public function runOnce()
	{
		$cTime		= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		$count		= 0;
		foreach ($this->_loopCbs as $cbObj) {
			$count	+= call_user_func_array($cbObj, array());
		}
		
		if ($count > 0) {
			$this->_lastMsg	= $cTime;
		} elseif (($this->_lastMsg + $this->_maxIdle) < $cTime) {
			//slow down we have not received a message in awhile
			usleep(10000);
		}
		return $count;
	}
	public function terminate($throw=false)
	{
		$this->_runStatus	= false;
		return $this;
	}
	protected function resetRunStatus()
	{
		if (count($this->_loopCbs) > 0) {
			$this->_runStatus		= true;
		} else {
			$this->_runStatus		= false;
		}
	}
}