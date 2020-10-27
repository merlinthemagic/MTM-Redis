<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Tracking extends Strings
{
	protected $_trackedKeys=array();
	
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
}