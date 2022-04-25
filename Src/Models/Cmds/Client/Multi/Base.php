<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Multi;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Client\Base
{
	protected $_baseCmd="MULTI";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
	public function getSocket()
	{
		return $this->getClient()->getMainSocket();
	}
}