<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Channel\Punsubscribe;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Channel\Base
{
	protected $_baseCmd="PUNSUBSCRIBE";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
	public function getSocket()
	{
		return $this->getClient()->getSubSocket();
	}
}