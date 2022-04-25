<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Base
{
	protected $_dbObj=null;

	public function __construct($dbObj)
	{
		$this->_dbObj	= $dbObj;
		parent::__construct();
	}
	public function getDb()
	{
		return $this->_dbObj;
	}
	public function getClient()
	{
		return $this->getDb()->getClient();
	}
	public function getSocket()
	{
		return $this->getClient()->getMainSocket();
	}
	public function selectDb()
	{
		$this->getDb()->selectDb();
		return $this;
	}
}