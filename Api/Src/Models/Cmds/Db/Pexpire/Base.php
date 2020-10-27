<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\Pexpire;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Db\Base
{
	protected $_baseCmd="PEXPIRE";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}