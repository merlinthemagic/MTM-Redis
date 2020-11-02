<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Lists;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Base
{
	protected $_listObj=null;

	public function __construct($listObj)
	{
		$this->_listObj	= $listObj;
		parent::__construct();
	}
	public function getList()
	{
		return $this->_listObj;
	}
	public function getDb()
	{
		return $this->getList()->getDb();
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
	protected function preTracking()
	{
		if ($this->getList()->isTracking() === false) {
			if ($this->getSocket()->getTrackMode() === "OPTOUT") {
				$this->selectDb();
				$this->getSocket()->clientCaching(false)->exec(true);
			}
		}
		return $this;
	}
	protected function postTracking()
	{
		if ($this->getList()->isTracking() === true) {
			if ($this->getSocket()->getTrackMode() === "OPTIN") {
				$this->selectDb();
				$this->getSocket()->clientCaching(true)->exec(true);
				$this->getList()->lLen()->exec(true);
			}
		}
		return $this;
	}
}