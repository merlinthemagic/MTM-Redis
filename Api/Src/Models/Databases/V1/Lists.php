<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Lists extends Base
{
	public function lPush($key=null, $value=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Lpush\V1($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function rPush($key=null, $value=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Rpush\V1($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
}