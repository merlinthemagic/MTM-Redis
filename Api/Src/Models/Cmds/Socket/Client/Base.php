<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Client;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Socket\Base
{
	protected $_baseCmd="CLIENT";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}