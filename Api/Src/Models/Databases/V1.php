<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases;

class V1 extends Base
{
	public function newTransaction()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Multi($this);
		return $cmdObj;
	}
	public function newWatchMulti($watchKeys=array(), $cmdObjs=array())
	{
		$wmObj		= new \MTM\RedisApi\Models\Cmds\WatchMulti($this);
		foreach ($watchKeys as $watchKey) {
			$wmObj->addWatch($watchKey);
		}
		foreach ($cmdObjs as $cmdObj) {
			$wmObj->addCmd($cmdObj);
		}
		return $wmObj;
	}
	public function watch($key=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Watch($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function unwatch()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Unwatch($this);
		return $cmdObj;
	}
	public function exists($key=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Exists($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function get($key=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Get($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function set($key=null, $value=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Set($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function append($key, $value)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Append($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function setNx($key, $value, $throw=false)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\SetNx($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function delete($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Delete($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function terminate($throw=true)
	{
		$errObj	= null;
		if ($errObj === null) {
			return $this;
		} elseif ($throw === true) {
			throw $errObj;
		} else {
			return $errObj;
		}
		return $this;
	}
}