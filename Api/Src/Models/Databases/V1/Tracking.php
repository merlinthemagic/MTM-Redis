<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Tracking extends Strings
{
	protected $_trackedKeys=array();
	
	public function trackingPreCmd($keyObj)
	{
		//issues commands to make sure the key maintains the correct client caching
		//before a command is issued
		if ($this->getSocket()->isTracked() === true) {
			if ($keyObj->isTracking() === false) {
				if ($this->getSocket()->getTrackMode() === "OPTOUT") {
					$this->selectDb();
					$this->getSocket()->clientCaching(false)->exec(true);
				}
			}
		}
		return $this;
	}
	public function trackingPostCmd($keyObj)
	{
		//issues commands to make sure the key maintains the correct client caching
		//after a command was issued
		//TODO: deal with multi commands
		if ($this->getSocket()->isTracked() === true) {
			if ($keyObj->isTracking() === true) {
				if ($this->getSocket()->getTrackMode() === "OPTIN") {
					$this->selectDb()->getSocket()->clientCaching(true)->exec(true);
					$this->type($keyObj->getKey())->exec(true);
				}
			}
		}
		return $this;
	}
	public function trackKey($keyObj)
	{
		if (array_key_exists($keyObj->getKey(), $this->_trackedKeys) === false) {
			if (count($this->_trackedKeys) === 0) {
				$this->getClient()->getChannel("__redis__:invalidate")->subscribe()->addCb($this, "trackedKeyCb");
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
				$this->getClient()->getChannel("__redis__:invalidate")->removeCb($this, "trackedKeyCb");
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
}