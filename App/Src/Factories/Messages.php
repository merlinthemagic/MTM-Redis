<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Factories;

class Messages extends Base
{
	protected $_penEgress=array();
	protected $_penIngress=array();
	
	public function getIngressV1()
	{
		$reqObj		= new \MTM\Redis\Messages\Ingress\V1();
		return $reqObj;
	}
	public function getEgressV1()
	{
		$reqObj		= new \MTM\Redis\Messages\Egress\V1();
		return $reqObj;
	}
	public function addEgress($reqObj)
	{
		$this->_penEgress[$reqObj->getMsgId()]	= $reqObj;
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
}