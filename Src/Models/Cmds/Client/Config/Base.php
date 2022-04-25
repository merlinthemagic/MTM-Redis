<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Config;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Client\Base
{
	protected $_baseCmd="CONFIG";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
	public function getSocket()
	{
		return $this->getClient()->getMainSocket();
	}
}