<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream\Xlen;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Stream\Base
{
	protected $_baseCmd="XLEN";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}