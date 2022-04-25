<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Base extends \MTM\RedisApi\Models\Databases\Base
{
	protected $_clientObj=null;
	protected $_id=null;
	
	public function __construct($clientObj, $id)
	{
		$this->_clientObj	= $clientObj;
		$this->_id			= $id;
		parent::__construct();
	}
	public function __destruct()
	{
		//dont want to throw in shutdown
		$this->terminate(false);
	}
	public function getId()
	{
		return $this->_id;
	}
	public function getClient()
	{
		return $this->_clientObj;
	}
	public function getSocket()
	{
		return $this->getClient()->getMainSocket();
	}
}