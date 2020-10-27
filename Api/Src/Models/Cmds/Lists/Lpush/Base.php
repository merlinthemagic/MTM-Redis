<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Lists\Lpush;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Lists\Base
{
	protected $_baseCmd="LPUSH";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}