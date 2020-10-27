<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Lists\Lpop;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Lists\Base
{
	//get first element
	
	protected $_baseCmd="LPOP";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}