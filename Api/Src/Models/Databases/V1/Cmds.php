<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Cmds extends Base
{
	public function selectDb()
	{
		$this->getSocket()->selectDb($this->getId());
		return $this;
	}
	public function flush($async=false)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Flush\V1($this);
		$cmdObj->setAsync($async);
		return $cmdObj;
	}
	public function exists($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Exists\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function delete($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Del\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function expire($key, $secs)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Expire\V1($this);
		$cmdObj->setKey($key)->setExpire($secs);
		return $cmdObj;
	}
	public function expireAt($key, $epoch)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\ExpireAt\V1($this);
		$cmdObj->setKey($key)->setExpire($epoch);
		return $cmdObj;
	}
	public function ttl($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Ttl\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function pExpire($key, $ms)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Pexpire\V1($this);
		$cmdObj->setKey($key)->setExpire($ms);
		return $cmdObj;
	}
	public function pTtl($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Pttl\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function type($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Type\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function objectIdleTime($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Objects\IdleTime\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
}