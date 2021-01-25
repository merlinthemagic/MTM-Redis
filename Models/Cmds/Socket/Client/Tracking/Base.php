<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Client\Tracking;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Socket\Client\Base
{
	protected $_clientCmd="TRACKING";

	public function getClientCmd()
	{
		return $this->_clientCmd;
	}
}