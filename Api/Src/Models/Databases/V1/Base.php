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
	public function getClient()
	{
		return $this->_clientObj;
	}
	public function getParent()
	{
		//temp alias
		return $this->_clientObj;
	}
	public function getId()
	{
		return $this->_id;
	}
}