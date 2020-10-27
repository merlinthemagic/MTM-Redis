<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Lists;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_dbObj=null;
	protected $_key=null;
	
	public function __construct($dbObj, $key)
	{
		$this->_dbObj		= $dbObj;
		$this->_key			= $key;
		parent::__construct();
	}
	public function __destruct()
	{
		//dont want to throw in shutdown
		$this->terminate(false);
	}
	public function getDb()
	{
		return $this->_dbObj;
	}
	public function getKey()
	{
		return $this->_key;
	}
}