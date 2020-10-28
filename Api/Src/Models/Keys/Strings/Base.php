<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Keys\Strings;

abstract class Base extends \MTM\RedisApi\Models\Keys\Base
{
	protected $_dbObj=null;
	protected $_key=null;
	
	public function __construct($dbObj, $key)
	{
		if (is_string($key) === false && is_int($key) === false) {
			throw new \Exception("Invalid key");
		}
		$this->_dbObj		= $dbObj;
		$this->_key			= $key;
		parent::__construct();
	}
	public function __destruct()
	{
		$this->terminate(false);
	}
	public function getKey()
	{
		return $this->_key;
	}
	public function getDb()
	{
		return $this->_dbObj;
	}
	public function getClient()
	{
		return $this->getDb()->getParent();
	}
	public function getSocket()
	{
		return $this->getClient()->getMainSocket();
	}
}