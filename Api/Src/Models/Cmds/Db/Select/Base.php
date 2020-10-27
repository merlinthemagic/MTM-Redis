<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Db\Select;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Db\Base
{
	protected $_baseCmd="SELECT";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
	public function selectDb()
	{
		//overide the select method, we are used for that
		throw new \Exception("Select not allowed for select command");
	}
}