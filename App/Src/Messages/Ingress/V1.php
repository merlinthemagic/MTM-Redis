<?php
//� 2020 Martin Peter Madsen
namespace MTM\Redis\Messages\Ingress;

class V1 extends Base
{
	protected $_clientObj=null;
	
	public function setClient($cObj)
	{
		$this->_clientObj	= $cObj;
		return $this;
	}
	public function getClient()
	{
		return $this->_clientObj;
	}
	public function getSrcIp()
	{
		if (preg_match("/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\:([0-9]+)/", $this->getSocket()->getAddress(), $raw) === 1) {
			return $raw[1];
		} else {
			return $this->getSocket()->getAddress();
		}
	}
	public function parseIngress($msgObj)
	{
		$this->setMsgId($msgObj->msgId)->setReq($msgObj->data);
		$this->setL1($msgObj->rL1)->setL2($msgObj->rL2)->setL3($msgObj->rL3)->setL4($msgObj->rL4);
		$this->setTimeout(intval(round(($msgObj->expire - \MTM\Utilities\Factories::getTime()->getMicroEpoch()) * 1000)));
		return $this;
	}
	public function getResponseObj()
	{
		$rObj			= new \stdClass();
		$rObj->type		= "response";
		$rObj->version	= 1;
		$rObj->msgId	= $this->getMsgId();
		$rObj->time		= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		if ($this->getException() === null) {
			$rObj->error	= null;
		} else {
			$rObj->error		= new \stdClass();
			$rObj->error->msg	= $this->getException()->getMessage();
			$rObj->error->code	= $this->getException()->getCode();
		}
		$rObj->data		= $this->getResp();
		return $rObj;
	}
	public function send()
	{
		if ($this->isRespDone() === false) {
			$this->getClient()->getSocket()->sendMessage(json_encode($this->getResponseObj(), JSON_PRETTY_PRINT));
			$this->setRespDone();
		}
		return $this;
	}
}