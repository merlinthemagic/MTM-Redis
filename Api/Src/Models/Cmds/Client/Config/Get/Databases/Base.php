<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Config\Get\Databases;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Client\Config\Get\Base
{
	protected $_subCmd="DATABASES";
	
	public function getSubCmd()
	{
		return $this->_subCmd;
	}
}