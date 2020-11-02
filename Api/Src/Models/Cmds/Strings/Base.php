<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Base
{
	protected $_stringObj=null;

	public function __construct($stringObj)
	{
		$this->_stringObj	= $stringObj;
		parent::__construct();
	}
	public function getString()
	{
		return $this->_stringObj;
	}
	public function getDb()
	{
		return $this->getString()->getDb();
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
		//certain commands must lead with a caching command in order to maintain caching state
		if ($this->getString()->isTracking() === false) {
			if ($this->getSocket()->getTrackMode() === "OPTOUT") {
				$this->selectDb();
				$this->getSocket()->clientCaching(false)->exec(true);
			}
		}
		return $this;
	}
	protected function postTracking()
	{
		//certain commands must opt in again in order to maintain caching state
		if ($this->getString()->isTracking() === true) {
			if ($this->getSocket()->getTrackMode() === "OPTIN") {
				$this->selectDb();
				$this->getSocket()->clientCaching(true)->exec(true);
				$this->getString()->strLen()->exec(true);
			}
		}
		return $this;
	}
}