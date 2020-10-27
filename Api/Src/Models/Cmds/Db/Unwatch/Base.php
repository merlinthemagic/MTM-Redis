<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\Unwatch;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Db\Base
{
	protected $_baseCmd="UNWATCH";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}