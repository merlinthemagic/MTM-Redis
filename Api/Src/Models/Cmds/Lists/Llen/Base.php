<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Lists\Llen;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Lists\Base
{
	protected $_baseCmd="LLEN";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}