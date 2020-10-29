<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class StringCmds extends Streams
{
	public function getString($key)
	{
		$strObj		= new \MTM\RedisApi\Models\Keys\Strings\V1($this, $key);
		return $strObj;
	}
	public function get($key=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Get\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function set($key=null, $value=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Set\V1($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function setNx($key, $value)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\SetNx\V1($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function setDx($key, $value)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\SetDx\V1($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function setEx($key, $value, $secs)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\SetEx\V1($this);
		$cmdObj->setKey($key)->setValue($value)->setExpire($secs);
		return $cmdObj;
	}
	public function pSetEx($key, $value, $ms)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\PsetEx\V1($this);
		$cmdObj->setKey($key)->setValue($value)->setExpire($ms);
		return $cmdObj;
	}
}