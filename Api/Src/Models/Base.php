<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models;

abstract class Base
{
	protected $_guid=null;
	protected $_isInit=false;
	protected $_isTerm=false;
	
	public function __construct()
	{
		$this->_guid	=  \MTM\Utilities\Factories::getGuids()->getV4()->get(false);
	}
	public function getGuid()
	{
		return $this->_guid;
	}
	public function isInit()
	{
		return $this->_isInit;
	}
	public function setInit()
	{
		$this->_isInit = true;
		return $this;
	}
	public function isTerm()
	{
		return $this->_isTerm;
	}
	public function setTerm()
	{
		$this->_isTerm = true;
		return $this;
	}
}