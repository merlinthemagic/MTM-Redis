<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Quit;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Socket\Base
{
	protected $_baseCmd="QUIT";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}