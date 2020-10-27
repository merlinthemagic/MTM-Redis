<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream\Xinfo;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Stream\Base
{
	protected $_baseCmd="XINFO";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}