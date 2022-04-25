<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\Objects\IdleTime;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Db\Objects\Base
{
	protected $_objCmd="IDLETIME";

	public function getObjectCmd()
	{
		return $this->_objCmd;
	}
}