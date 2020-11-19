<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Messages;

abstract class Base
{
	protected $_timeout=10000;
	
	protected $_msgId=null;
	
	protected $_rL1=null;
	protected $_rL2=null;
	protected $_rL3=null;
	protected $_rL4=null;
	protected $_rL5=null;
	protected $_rL6=null;

	protected $_reqDone=false;
	protected $_respDone=false;
	
	protected $_reqData=null;
	protected $_respData=null;	
	protected $_exObj=null;
	
	public function __construct()
	{
		$this->_reqData		= new \stdClass();
		$this->_respData	= new \stdClass();
	}
	public function getMsgId()
	{
		return $this->_msgId;
	}
	public function getTimeout()
	{
		return $this->_timeout;
	}
	public function setTimeout($ms)
	{
		if ($this->getTimeout() != $ms) {
			if (is_int($ms) === false) {
				throw new \Exception("Invalid input");
			}
			$this->_timeout	= $ms;
		}
		return $this;
	}
	public function getException()
	{
		return $this->_exObj;
	}
	public function setException($e)
	{
		$this->_exObj	= $e;
		return $this;
	}
	public function setL1($value)
	{
		$this->_rL1	= $value;
		return $this;
	}
	public function setL2($value)
	{
		$this->_rL2	= $value;
		return $this;
	}
	public function setL3($value)
	{
		$this->_rL3	= $value;
		return $this;
	}
	public function setL4($value)
	{
		$this->_rL4	= $value;
		return $this;
	}
	public function getL1()
	{
		return $this->_rL1;
	}
	public function getL2()
	{
		return $this->_rL2;
	}
	public function getL3()
	{
		return $this->_rL3;
	}
	public function getL4()
	{
		return $this->_rL4;
	}
	public function isReqDone()
	{
		return $this->_reqDone;
	}
	public function setReqDone()
	{
		$this->_reqDone		= true;
		return $this;
	}
	public function setReq($value)
	{
		$this->_reqData	= $value;
		return $this;
	}
	public function addReq($key, $value)
	{
		$this->_reqData->$key	= $value;
		return $this;
	}
	public function getReq($key=null, $throw=false)
	{
		if ($key === null) {
			return $this->_reqData;
		} elseif (property_exists($this->_reqData, $key) === true) {
			return $this->_reqData->$key;
		} elseif ($throw === true) {
			throw new \Exception("Request key does not exist: " . $key);
		} else {
			return null;
		}
	}
	public function isRespDone()
	{
		return $this->_respDone;
	}
	public function setRespDone()
	{
		$this->_respDone		= true;
		return $this;
	}
	public function setResp($value)
	{
		$this->_respData	= $value;
		return $this;
	}
	public function addResp($key, $value)
	{
		$this->_respData->$key	= $value;
		return $this;
	}
	public function getResp($key=null, $throw=false)
	{
		if ($key === null) {
			return $this->_respData;
		} elseif (property_exists($this->_respData, $key) === true) {
			return $this->_respData->$key;
		} elseif ($throw === true) {
			throw new \Exception("Response key does not exist: " . $key);
		} else {
			return null;
		}
	}
}