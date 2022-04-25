<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Strings\SetNx;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Strings\Base
{
	//SET if Not eXists
	protected $_baseCmd="SETNX";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}