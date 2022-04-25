<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Auth;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Socket\Base
{
	protected $_baseCmd="AUTH";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}