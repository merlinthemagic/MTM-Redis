<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\SetNxPx;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Strings\Base
{
	//SET if Not eXists with expire
	//used for locks
	//src: https://redis.io/topics/distlock
	protected $_baseCmd="SET";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}