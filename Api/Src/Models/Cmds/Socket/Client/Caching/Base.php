<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Client\Caching;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Socket\Client\Base
{
	protected $_clientCmd="CACHING";

	public function getClientCmd()
	{
		return $this->_clientCmd;
	}
}