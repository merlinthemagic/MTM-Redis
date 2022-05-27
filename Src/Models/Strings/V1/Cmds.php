<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Strings\V1;

abstract class Cmds extends Base
{
	public function get()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\Get\V1($this);
		return $cmdObj;
	}
	public function exists()
	{
		$cmdObj		= $this->getDb()->exists($this->getKey());
		return $cmdObj;
	}
	public function strLen()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\StrLen\V1($this);
		return $cmdObj;
	}
	public function idleTime()
	{
		//how long since the last read or write?
		$cmdObj		= $this->getDb()->objectIdleTime($this->getKey());
		return $cmdObj;
	}
	public function set($value=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\Set\V1($this);
		$cmdObj->setValue($value);
		return $cmdObj;
	}
	public function setKeepTTL($value=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\SetKeepTTL\V1($this);
		$cmdObj->setValue($value);
		return $cmdObj;
	}
	public function setNx($value)
	{
		//SET if Not eXists
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\SetNx\V1($this);
		$cmdObj->setValue($value);
		return $cmdObj;
	}
	public function setNxPx($value, $ms)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\SetNxPx\V1($this);
		$cmdObj->setValue($value)->setExpire($ms);
		return $cmdObj;
	}
	public function setDx($value)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\SetDx\V1($this);
		$cmdObj->setValue($value);
		return $cmdObj;
	}
	public function setEx($value, $secs)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\SetEx\V1($this);
		$cmdObj->setValue($value)->setExpire($secs);
		return $cmdObj;
	}
	public function pSetEx($value, $ms)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\PsetEx\V1($this);
		$cmdObj->setValue($value)->setExpire($ms);
		return $cmdObj;
	}
	public function expire($secs)
	{
		$cmdObj		= $this->getDb()->expire($this->getKey(), $secs);
		return $cmdObj;
	}
	public function pExpire($ms)
	{
		$cmdObj		= $this->getDb()->pExpire($this->getKey(), $ms);
		return $cmdObj;
	}
	public function pTtl($throw=false)
	{
		//get time to life -1 === no timer
		return $this->getDb()->pTtl($this->getKey())->exec($throw);
	}
	public function delete()
	{
		//delete self
		$this->getDb()->deleteString($this);
		return $this;
	}
	public function delMatchValue($value)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Strings\DelMatchValue\V1($this);
		$cmdObj->setValue($value);
		return $cmdObj;
	}
}