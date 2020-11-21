<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Models\Clients\V1;

abstract class Tracking extends Cmds
{
	protected $_keyObjs=array();
	
	public function trackKey($keyObj)
	{
		$keyObj->setUpdateCb($this, "trackUpdate")->setDeleteCb($this, "trackDelete")->enableTracking();
		$this->_keyObjs[]	= $keyObj;
		return $this;
	}
	public function unTrackKey($keyObj)
	{
		foreach ($this->_keyObjs as $index => $eObj) {
			if ($eObj->getGuid() === $keyObj->getGuid()) {
				$keyObj->removeUpdateCb($this, "trackUpdate")->removeDeleteCb($this, "trackDelete");
				unset($this->_keyObjs[$index]);
				break;
			}
		}
		return $this;
	}
	public function trackUpdate($keyObj)
	{
		$reqObj		= \MTM\Redis\Facts::getMessages()->getEgressV1($this);
		if ($keyObj instanceof \MTM\RedisApi\Models\Strings\Base === true) {
			$reqObj->setL1("Strings")->setL2("Caching")->setL3("Invalidate");
		} else {
			throw new \Exception("Not handled for class: ".get_class($keyObj));
		}
		$reqObj->addReq("dbId", $keyObj->getDb()->getId());
		$reqObj->addReq("key", $keyObj->getKey());
		$reqObj->setTimeout(0);
		$reqObj->send();
	}
	public function trackDelete($keyObj)
	{
		$reqObj		= \MTM\Redis\Facts::getMessages()->getEgressV1($this);
		if ($keyObj instanceof \MTM\RedisApi\Models\Strings\Base === true) {
			$reqObj->setL1("Strings")->setL2("Caching")->setL3("Delete");
		} else {
			throw new \Exception("Not handled for class: ".get_class($keyObj));
		}
		$reqObj->addReq("dbId", $keyObj->getDb()->getId());
		$reqObj->addReq("key", $keyObj->getKey());
		$reqObj->setTimeout(0);
		$reqObj->send();
	}
}