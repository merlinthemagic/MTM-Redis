<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases;

class V1 extends Base
{
	protected $_trackedKeys=array();
	
	public function getKey($key)
	{
		$keyObj		= new \MTM\RedisApi\Models\Keys\V1($this, $key);
		return $keyObj;
	}
	public function newTransaction()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Multi($this);
		return $cmdObj;
	}
	public function newWatchMulti($watchObjs=array(), $cmdObjs=array())
	{
		$wmObj		= new \MTM\RedisApi\Models\Cmds\WatchMulti($this);
		foreach ($watchObjs as $watchObj) {
			$wmObj->addWatch($watchObj);
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
	public function setNx($key, $value)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\SetNx($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function setDx($key, $value)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\SetDx($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function setEx($key, $value, $secs)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\SetEx($this);
		$cmdObj->setKey($key)->setValue($value)->setExpire($secs);
		return $cmdObj;
	}
	public function append($key, $value)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Append($this);
		$cmdObj->setKey($key)->setValue($value);
		return $cmdObj;
	}
	public function delete($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Delete($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function expire($key, $secs)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Expire($this);
		$cmdObj->setKey($key)->setExpire($secs);
		return $cmdObj;
	}
	public function ttl($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Ttl($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function pExpire($key, $ms)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Pexpire($this);
		$cmdObj->setKey($key)->setExpire($ms);
		return $cmdObj;
	}
	public function pTtl($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Pttl($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function trackKey($keyObj)
	{
		if (array_key_exists($keyObj->getKey(), $this->_trackedKeys) === false) {
			if (count($this->_trackedKeys) === 0) {
				$this->getParent()->getChannel("__redis__:invalidate")->subscribe()->addCb($this, "trackedKeyCb");
			}
			$this->_trackedKeys[$keyObj->getKey()]	= $keyObj;
		}
		return $this;
	}
	public function untrackKey($keyObj)
	{
		if (array_key_exists($keyObj->getKey(), $this->_trackedKeys) === true) {
			unset($this->_trackedKeys[$keyObj->getKey()]);
			if (count($this->_trackedKeys) === 0) {
				$this->getParent()->getChannel("__redis__:invalidate")->removeCb($this, "trackedKeyCb");
			}
		}
		return $this;
	}
	public function trackedKeyCb($chanObj, $msgObj)
	{
		if (is_array($msgObj->payload) === true) {
			foreach ($msgObj->payload as $key) {
				if (array_key_exists($key, $this->_trackedKeys) === true) {
					$this->_trackedKeys[$key]->trackInvalidated();
				}
			}
		} elseif ($msgObj->payload === "FLUSHALL") {
			foreach ($this->_trackedKeys as $keyObj) {
				$keyObj->trackInvalidated();
			}
		} else {
			throw new \Exception("Not handled for payload: ".$msgObj->payload);
		}
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