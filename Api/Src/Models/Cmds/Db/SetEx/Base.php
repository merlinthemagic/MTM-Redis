<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\SetEx;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Db\Base
{
	protected $_baseCmd="SETEX";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}