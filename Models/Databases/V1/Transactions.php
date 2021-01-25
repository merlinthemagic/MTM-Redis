<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Transactions extends Tracking
{
	public function watch($key=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Watch\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function unwatch()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Unwatch\V1($this);
		return $cmdObj;
	}
}