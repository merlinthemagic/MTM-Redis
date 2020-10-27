<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Client\Id;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Socket\Client\Base
{
	protected $_clientCmd="ID";

	public function getClientCmd()
	{
		return $this->_clientCmd;
	}
}