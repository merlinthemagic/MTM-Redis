<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Channel\Subscribe;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Channel\Base
{
	protected $_baseCmd="SUBSCRIBE";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
	public function getSocket()
	{
		return $this->getClient()->getSubSocket();
	}
}