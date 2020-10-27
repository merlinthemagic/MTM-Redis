<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_respData=null;
	protected $_isExec=false;
	protected $_isQueued=false;
	protected $_exObj=null;
	
	public function setResponse($data)
	{
		$this->_respData	= $data;
		return $this;
	}
	public function getResponse($throw=false)
	{
		if ($this->getException() !== null && $throw === true) {
			throw $this->getException();
		} else {
			return $this->_respData;
		}
	}
	public function setException($e)
	{
		$this->_exObj	= $e;
		return $this;
	}
	public function getException()
	{
		return $this->_exObj;
	}
	public function isExec()
	{
		return $this->_isExec;
	}
	public function isQueued()
	{
		return $this->_isQueued;
	}
	public function reset()
	{
		$this->_respData	= null;
		$this->_isExec		= false;
		$this->_isQueued	= false;
		$this->_exObj		= null;
		return $this;
	}
}