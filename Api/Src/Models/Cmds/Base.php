<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_parentObj=null;
	protected $_baseCmd=null;
	protected $_respData=null;
	protected $_isExec=false;
	protected $_isQueued=false;
	protected $_exObj=null;
	
	public function __construct($dbObj)
	{
		$this->_parentObj	= $dbObj;
		parent::__construct();
	}
	public function getParent()
	{
		return $this->_parentObj;
	}
	public function getClient()
	{
		return $this->getParent()->getParent();
	}
	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
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
}