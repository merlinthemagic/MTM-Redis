<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Lists\V1;

abstract class Cmds extends Base
{
	public function lPush($value=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Lists\Lpush\V1($this);
		$cmdObj->setValue($value);
		return $cmdObj;
	}
	public function rPush($value=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Lists\Rpush\V1($this);
		$cmdObj->setValue($value);
		return $cmdObj;
	}
	public function lPop()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Lists\Lpop\V1($this);
		return $cmdObj;
	}
	public function rPop()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Lists\Rpop\V1($this);
		return $cmdObj;
	}
	public function lLen()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Lists\Llen\V1($this);
		return $cmdObj;
	}
	public function delete()
	{
		//delete self
		$this->getDb()->deleteList($this);
		return $this;
	}
}