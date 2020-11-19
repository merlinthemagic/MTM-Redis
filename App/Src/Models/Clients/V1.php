<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Models\Clients;

class V1 extends Base
{
	protected $_sockObj=null;
	protected $_redisObj=null;
	protected $_keyObjs=array();
	
	public function setSocket($sockObj)
	{
		$this->_sockObj		= $sockObj;
		return $this;
	}
	public function getSocket()
	{
		return $this->_sockObj;
	}
	public function setRedis($redisObj)
	{
		$this->_redisObj	= $redisObj;
		return $this;
	}
	public function getRedis()
	{
		return $this->_redisObj;
	}
	public function trackKey($keyObj)
	{
		$keyObj->setUpdateCb($this, "redisUpdate")->setDeleteCb($this, "redisDelete")->enableTracking();
		$this->_keyObjs[]	= $keyObj;
		return $this;
	}
	public function unTrackKey($keyObj)
	{
		foreach ($this->_keyObjs as $index => $eObj) {
			if ($eObj->getGuid() === $keyObj->getGuid()) {
				$keyObj->removeUpdateCb($this, "redisUpdate")->removeDeleteCb($this, "redisDelete");
				unset($this->_keyObjs[$index]);
				break;
			}
		}
		return $this;
	}
	public function redisUpdate($keyObj)
	{
		$reqObj		= \MTM\Redis\Facts::getMessages()->getEgressV1();
		if ($keyObj instanceof \MTM\RedisApi\Models\Strings\Base === true) {
			$reqObj->setL1("Strings")->setL2("Caching")->setL3("Invalidate");
		} else {
			throw new \Exception("Not handled for class: ".get_class($keyObj));
		}
		$reqObj->setClient($this)->addReq("dbId", $keyObj->getDb()->getId());
		$reqObj->addReq("key", $keyObj->getKey());
		$reqObj->setTimeout(0);
		$reqObj->send();
	}
	public function redisDelete($keyObj)
	{
		$reqObj		= \MTM\Redis\Facts::getMessages()->getEgressV1();
		if ($keyObj instanceof \MTM\RedisApi\Models\Strings\Base === true) {
			$reqObj->setL1("Strings")->setL2("Caching")->setL3("Delete");
		} else {
			throw new \Exception("Not handled for class: ".get_class($keyObj));
		}
		$reqObj->setClient($this)->addReq("dbId", $keyObj->getDb()->getId());
		$reqObj->addReq("key", $keyObj->getKey());
		$reqObj->setTimeout(0);
		$reqObj->send();
	}
	public function terminate($throw=false)
	{
		foreach ($this->_keyObjs as $keyObj) {
			$this->unTrackKey($keyObj);
		}
		if ($this->getSocket() !== null) {
			$this->getSocket()->terminate(false);
		}
		if ($this->getRedis() !== null) {
			$this->getRedis()->terminate(false);
		}
	}
}