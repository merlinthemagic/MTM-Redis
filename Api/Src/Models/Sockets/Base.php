<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	public function __construct($clientObj)
	{
		$this->_parentObj	= $clientObj;
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
}