<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Groups;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_parentObj=null;
	protected $_name=null;
	
	public function __construct($streamObj, $name)
	{
		$this->_parentObj	= $streamObj;
		$this->_name		= $name;
		parent::__construct();
	}
	public function getParent()
	{
		return $this->_parentObj;
	}
	public function getClient()
	{
		return $this->getParent()->getParent()->getPhpRedis();
	}
	public function getName()
	{
		return $this->_name;
	}
	protected function getMsgObj()
	{
		$msgObj				= new \stdClass();
		$msgObj->id			= null;
		$msgObj->payload	= null;
		return $msgObj;
	}
}