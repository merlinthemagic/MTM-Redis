<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Streams;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_clientObj=null;
	protected $_key=null;
	
	public function __construct($clientObj, $key)
	{
		$this->_clientObj	= $clientObj;
		$this->_key			= $key;
		parent::__construct();
	}
	public function __destruct()
	{
		//dont want to throw in shutdown
		$this->terminate(false);
	}
	public function getClient()
	{
		return $this->_clientObj;
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