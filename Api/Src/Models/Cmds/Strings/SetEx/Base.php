<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\SetEx;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Strings\Base
{
	protected $_baseCmd="SETEX";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}