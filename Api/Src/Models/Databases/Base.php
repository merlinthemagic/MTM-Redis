<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_parentObj=null;
	protected $_id=null;
	
	public function __construct($clientObj, $id)
	{
		$this->_parentObj	= $clientObj;
		$this->_id			= $id;
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
	public function getId()
	{
		return $this->_id;
	}
}