<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Config\Get;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Client\Config\Base
{
	protected $_confCmd="GET";
	
	public function getConfCmd()
	{
		return $this->_confCmd;
	}
}