<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\SetKeepTTL;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Strings\Base
{
	//KEEPTTL is appended after key, data
	protected $_baseCmd="SET";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}