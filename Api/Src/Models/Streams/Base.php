<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Streams;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_parentObj=null;
	protected $_key=null;
	
	public function __construct($clientObj, $key)
	{
		$this->_parentObj	= $clientObj;
		$this->_key			= $key;
		parent::__construct();
	}
	public function __destruct()
	{
		//dont want to throw in shutdown
		$this->terminate(false);
	}
	public function getParent()
	{
		return $this->_parentObj;
	}
	public function getKey()
	{
		return $this->_key;
	}
	protected function getMsgObj()
	{
		$msgObj				= new \stdClass();
		$msgObj->id			= null;
		$msgObj->payload	= null;
		return $msgObj;
	}
}