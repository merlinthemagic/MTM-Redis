<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Groups;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Base
{
	protected $_grpObj=null;

	public function __construct($grpObj)
	{
		$this->_grpObj	= $grpObj;
		parent::__construct();
	}
	public function getGroup()
	{
		return $this->_grpObj;
	}
	public function getStream()
	{
		return $this->getGroup()->getStream();
	}
	public function getDb()
	{
		return $this->getStream()->getDb();
	}
	public function getClient()
	{
		return $this->getDb()->getParent();
	}
	public function getSocket()
	{
		return $this->getClient()->getMainSocket();
	}
	public function selectDb()
	{
		$this->getSocket()->selectDb($this->getDb()->getId());
		return $this;
	}
}