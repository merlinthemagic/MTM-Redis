<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Factories;

class Messages extends Base
{
	protected $_isRegi=false;
	protected $_nextCleanup=4294967294;
	protected $_penEgress=array();
	protected $_penIngress=array();
	
	public function getIngressV1($clientObj=null)
	{
		$reqObj		= new \MTM\Redis\Messages\Ingress\V1();
		$reqObj->setClient($clientObj);
		return $reqObj;
	}
	public function getEgressV1($clientObj=null)
	{
		$reqObj		= new \MTM\Redis\Messages\Egress\V1();
		$reqObj->setClient($clientObj);
		return $reqObj;
	}
	public function addEgress($reqObj)
	{
		$this->_penEgress[$reqObj->getMsgId()]	= $reqObj;
		
		$nc	= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + ($reqObj->getTimeout() / 1000);
		if ($this->_nextCleanup > $nc) {
			$this->_nextCleanup	= $nc;
		}
		if ($this->_isRegi === false) {
			\MTM\Redis\Facts::getProcess()->addLoopCb($this, "cleanUp");
			$this->_isRegi	= true;
		}
		return $this;
	}
	public function getEgressById($msgId, $throw=false)
	{
		if (array_key_exists($msgId, $this->_penEgress) === true) {
			$reqObj	= $this->_penEgress[$msgId];
			unset($this->_penEgress[$msgId]);
			return $reqObj;
		} elseif ($throw === true) {
			throw new \Exception("No pending egress message with ID: ".$msgId);
		} else {
			return null;
		}
	}
	public function addIngress($reqObj)
	{
		$this->_penIngress[$reqObj->getMsgId()]	= $reqObj;
		return $this;
	}
	public function getIngressById($msgId, $throw=false)
	{
		if (array_key_exists($msgId, $this->_penIngress) === true) {
			$reqObj	= $this->_penIngress[$msgId];
			unset($this->_penIngress[$msgId]);
			return $reqObj;
		} elseif ($throw === true) {
			throw new \Exception("No pending ingress message with ID: ".$msgId);
		} else {
			return null;
		}
	}
	public function getNextIngress()
	{
		return array_shift($this->_penIngress);
	}
	public function cleanUp()
	{
		$cTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		if ($cTime > $this->_nextCleanup) {
			$this->_nextCleanup	= 4294967294;
			
			foreach ($this->_penEgress as $id => $reqObj) {
				$exTime	= $reqObj->getSendTime() + ($reqObj->getTimeout() / 1000);
				if ($exTime < $cTime) {
					unset($this->_penEgress[$id]);
					$reqObj->setException(new \Exception("Failed to receive a response in time"));
					$reqObj->setRespDone()->respCb();
				} elseif ($this->_nextCleanup > $exTime) {
					$this->_nextCleanup	= $exTime;
				}
			}
			
			if (count($this->_penEgress) === 0) {
				\MTM\Redis\Facts::getProcess()->removeLoopCb($this, "cleanUp");
				$this->_isRegi	= false;
			}
		}
	}
}