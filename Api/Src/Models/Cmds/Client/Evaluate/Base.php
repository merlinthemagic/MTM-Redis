<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Evaluate;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Client\Base
{
	protected $_baseCmd="EVAL";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
	public function getSocket()
	{
		return $this->getClient()->getMainSocket();
	}
}