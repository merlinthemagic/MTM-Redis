<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Base
{
	protected $_streamObj=null;

	public function __construct($streamObj)
	{
		$this->_streamObj	= $streamObj;
		parent::__construct();
	}
	public function getStream()
	{
		return $this->_streamObj;
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