<?php
//© 2020 Martin Peter Madsen
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
		$this->setMsgId($msgObj->msgId);
		if (property_exists($msgObj, "hash") === false) {
			throw new \Exception("Hash missing");
		}
		$hash	= $msgObj->hash;
		unset($msgObj->hash);

		$sign	= hash("sha256", json_encode($msgObj).$this->getClient()->getAuthSecret());
		if ($hash !== $sign) {
			throw new \Exception("Hash mismatch");
		}
		$this->setReq($msgObj->data)->setTimeout(intval($msgObj->timeout));
		$this->setL1($msgObj->rL1)->setL2($msgObj->rL2)->setL3($msgObj->rL3)->setL4($msgObj->rL4);
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
			$rTime	= ($this->getRecvTime() + ($this->getTimeout() / 1000)) - \MTM\Utilities\Factories::getTime()->getMicroEpoch();
			if ($rTime > 0) {
				$this->getClient()->getSocket()->sendMessage(json_encode($this->getResponseObj(), JSON_PRETTY_PRINT));
			}
			$this->setRespDone();
		}
		return $this;
	}
}