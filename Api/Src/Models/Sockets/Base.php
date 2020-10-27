<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_clientObj=null;
	
	public function __construct($clientObj)
	{
		$this->_clientObj	= $clientObj;
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
}