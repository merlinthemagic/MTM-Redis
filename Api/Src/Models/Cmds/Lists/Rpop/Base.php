<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Lists\Rpop;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Lists\Base
{
	//get last element
	
	protected $_baseCmd="RPOP";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}