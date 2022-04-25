<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Ping;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Socket\Base
{
	protected $_baseCmd="PING";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}