<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Messages\Egress;

class V1 extends Base
{
	protected $_clientObj=null;

	public function __destruct()
	{
		if ($this->getTimeout() > 0) {
			//make sure we are not in the pending list. 
			//could happen if the request is set, but we error before caling getResponse()
			\MTM\Redis\Facts::getMessages()->getPending($this->getMsgId(), false);
		}
	}
	public function setClient($cObj)
	{
		$this->_clientObj	= $cObj;
		return $this;
	}
	public function getClient()
	{
		return $this->_clientObj;
	}
	public function getRequestObj()
	{
		$rObj			= new \stdClass();
		$rObj->type		= "request";
		$rObj->version	= 1;
		$rObj->msgId	= $this->getMsgId();
		$rObj->time		= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		$rObj->expire	= ($rObj->time + ($this->getTimeout()/1000));
		$rObj->rL1		= $this->getL1();
		$rObj->rL2		= $this->getL2();
		$rObj->rL3		= $this->getL3();
		$rObj->rL4		= $this->getL4();
		$rObj->data		= $this->getReq();
		return $rObj;
	}
	public function socketResp($msgObj)
	{
		//parse
		if ($msgObj->error !== null) {
			$this->setException(new \Exception($msgObj->error->msg, $msgObj->error->code));
		}
		$this->setResp($msgObj->data)->setRespDone()->respCb();
	}
	public function send()
	{
		if ($this->isReqDone() === false) {
			$this->getClient()->getSocket()->sendMessage(json_encode($this->getRequestObj(), JSON_PRETTY_PRINT));
			if ($this->getTimeout() > 0) {
				\MTM\Redis\Facts::getMessages()->addEgress($this);
			}
			$this->setReqDone();
		}
		return $this;
	}
	public function getResponse($throw=false)
	{
		//use with caution, can result in a growing amount of nested loops
		//where the first request is resolved last, use respCb when at all possible
// 		if ($this->isRespDone() === false) {
// 			$this->send();
// 			if ($this->getTimeout() > 0) {
// 				$procObj	= \DC\SCC\Facts::getProcess();
// 				$tTime		= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + 2 + ($this->getTimeout() / 1000);
// 				while(true) {
// 					$procObj->runOnce();
// 					if ($this->isRespDone() === true) {
// 						break;
// 					} elseif (\MTM\Utilities\Factories::getTime()->getMicroEpoch() > $tTime) {
// 						\DC\SCC\Facts::getMessages()->getEgressById($this->getMsgId(), false);
// 						$this->setException(new \Exception("Response timed out"));
// 						break;
// 					}
// 				}
// 			} else {
// 				$this->setRespDone();
// 			}
// 		}
// 		if ($throw === true && $this->getException() !== null) {
// 			throw $this->getException();
// 		} else {
// 			return $this->getResp();
// 		}
	}
}