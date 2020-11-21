<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Messages\Egress;

class V1 extends Base
{
	protected $_clientObj=null;
	protected $_sendTime=null;

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
		$rObj->timeout	= $this->getTimeout();
		$rObj->rL1		= $this->getL1();
		$rObj->rL2		= $this->getL2();
		$rObj->rL3		= $this->getL3();
		$rObj->rL4		= $this->getL4();
		$rObj->data		= $this->getReq();
		$rObj->hash		= hash("sha256", json_encode($rObj).$this->getClient()->getAuthSecret());
		
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
			$this->_sendTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
			if ($this->getTimeout() > 0) {
				\MTM\Redis\Facts::getMessages()->addEgress($this);
			}
			$this->setReqDone();
		}
		return $this;
	}
	public function getSendTime()
	{
		return $this->_sendTime;
	}
	public function getResponse($throw=false)
	{
		//use with caution, can result in a growing amount of nested loops
		//where the first request is resolved last, use respCb when at all possible
		if ($this->isRespDone() === false) {
			$this->send();
			if ($this->getTimeout() > 0) {
				$procObj	= \MTM\Redis\Facts::getProcess();
				while(true) {
					$procObj->runOnce();
					if ($this->isRespDone() === true) {
						//worker will poll the egress messages and time us out if needed
						break;
					}
				}
			} else {
				$this->setRespDone();
			}
		}
		if ($throw === true && $this->getException() !== null) {
			throw $this->getException();
		} else {
			return $this->getResp();
		}
	}
}