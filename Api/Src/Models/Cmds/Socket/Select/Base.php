<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Select;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Db\Base
{
	protected $_baseCmd="SELECT";

	public function getBaseCmd()
	{
		return $this->_baseCmd;
	}
}