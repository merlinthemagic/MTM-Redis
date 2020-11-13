<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\Flush;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Db\Base
{
	protected $_baseCmd="FLUSH";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}