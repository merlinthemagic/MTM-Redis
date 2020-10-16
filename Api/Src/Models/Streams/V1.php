<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Streams;

class V1 extends Base
{
	protected $_groupObjs=array();
	
	public function __destruct()
	{
		foreach ($this->getGroups() as $grpObj) {
			try {
				$this->removeGroup($grpObj);
			} catch (\Exception $e) {
			}
		}
	}
	public function getGroups()
	{
		return array_values($this->_groupObjs);
	}
	public function addGroup($name)
	{
		if ($this->getGroupByName($name, false) !== null) {
			throw new \Exception("Group already exist: ".$name);
		}
		$grpObj		= new \MTM\RedisApi\Models\Groups\V1($this, $name);
		$this->getParent()->getPhpRedis()->xGroup("create", $this->getKey(), $name, 0, true);
		$this->_groupObjs[$grpObj->getGuid()]	= $grpObj;
		return $grpObj;
	}
	public function removeGroup($grpObj)
	{
		if (array_key_exists($grpObj->getGuid(), $this->_groupObjs) === true) {
			unset($this->_groupObjs[$grpObj->getGuid()]);
		} else {
			throw new \Exception("Group does not belong to this client");
		}
	}
	public function getGroupByName($name, $throw=false)
	{
		foreach ($this->_groupObjs as $grpObj) {
			if ($grpObj->getName() == $name) {
				return $chanObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("Group does not exist: ".$name);
		} else {
			return null;
		}
	}
	public function xLen($throw=true)
	{
		$msgCount	= $this->getParent()->getPhpRedis()->xLen($this->getKey());
		if (is_int($msgCount) === true) {
			return $msgCount;
		} elseif ($throw === true) {
			//dont know when this can happen, if stream does not exist 0 is returned
			throw new \Exception("xLen failed to retrieve a message count");
		} else {
			return null;
		}
	}
	protected function xInfo($throw=false)
	{
		$info	= $this->getParent()->getPhpRedis()->xInfo("STREAM", $this->getKey());
		if ($info !== false) {
			$info	= (object) $info;
			return $info;
		} elseif ($throw === true) {
			throw new \Exception("xInfo failed, likely stream does not exist");
		} else {
			return null;
		}
	}
	public function xAdd($fields=array(), $msgId="*", $throw=false)
	{
		$msgId	= $this->getParent()->getPhpRedis()->xAdd($this->getKey(), $msgId, $fields);
		if ($msgId !== false) {
			$msgObj				= $this->getMsgObj();
			$msgObj->id			= $msgId;
			$msgObj->payload	= $fields;
			return $msgObj;
		} elseif ($throw === true) {
			throw new \Exception("xAdd failed, likely using existing msg ID");
		} else {
			return null;
		}
	}
	public function xReadFirst($throw=false)
	{
		$iObj	= $this->xInfo(false);
		if ($iObj !== null) {
			if ($iObj->{"first-entry"} !== null) {
				$lastId		= array_keys($iObj->{"first-entry"});
				$lastId		= reset($lastId);
				$lastMsg	= reset($iObj->{"first-entry"});
				
				$msgObj				= $this->getMsgObj();
				$msgObj->id			= $lastId;
				$msgObj->payload	= $lastMsg;
				return $msgObj;
			} elseif ($throw === true) {
				throw new \Exception("xReadFirst failed, stream is empty");
			} else {
				return null;
			}
			
		} elseif ($throw === true) {
			throw new \Exception("xReadFirst failed, likely stream does not exist");
		} else {
			return null;
		}
	}
	public function xReadLast($throw=false)
	{
		$iObj	= $this->xInfo(false);
		if ($iObj !== null) {
			if ($iObj->{"last-entry"} !== null) {
				$lastId		= array_keys($iObj->{"last-entry"});
				$lastId		= reset($lastId);
				$lastMsg	= reset($iObj->{"last-entry"});
				
				$msgObj				= $this->getMsgObj();
				$msgObj->id			= $lastId;
				$msgObj->payload	= $lastMsg;
				return $msgObj;
			} elseif ($throw === true) {
				throw new \Exception("xReadLast failed, stream is empty");
			} else {
				return null;
			}
			
		} elseif ($throw === true) {
			throw new \Exception("xReadLast failed, likely stream does not exist");
		} else {
			return null;
		}
	}
	public function xRangeAll($throw=false)
	{
		$msgs	= $this->getParent()->getPhpRedis()->xRange($this->getKey(), "-", "+");
		if (is_array($msgs) === true) {
			$rObjs	= array();
			foreach ($msgs as $id => $payload) {
				$msgObj				= $this->getMsgObj();
				$msgObj->id			= $id;
				$msgObj->payload	= $payload;
				$rObjs[]			= $msgObj;
			}
			return $rObjs;
			
		} elseif ($throw === true) {
			//dont know when this can happen, if stream does not exist empty array is returned
			throw new \Exception("xRangeAll received unexpected response data");
		} else {
			return null;
		}
	}
	public function xDelByMsgObj($msgObj, $throw=false)
	{
		if ($msgObj instanceof \stdClass === true) {
			return $this->xDelByMsgId($msgObj->id, $throw);
		} else {
			throw new \Exception("invalid input");
		}
	}
	public function xDelByMsgId($msgId, $throw=false)
	{
		$delCount		= $this->getParent()->getPhpRedis()->xDel($this->getKey(), array($msgId));
		if ($delCount === 1) {
			return true;
		} elseif ($throw === true) {
			throw new \Exception("xDel expected 1 key be deleted, received: ".$delCount);
		} else {
			return false;
		}
	}
	public function xDelAll($throw=false)
	{
		$msgObjs	= $this->xRangeAll(true);
		if (count($msgObjs) > 0) {
			$ids	= array();
			foreach ($msgObjs as $msgObj) {
				$ids[]	= $msgObj->id;
			}
			$delCount	= $this->getParent()->getPhpRedis()->xDel($this->getKey(), $ids);
			if ($delCount === count($ids)) {
				return true;
			} elseif ($throw === true) {
				throw new \Exception("xDel expected ".count($ids)." key be deleted, received: ".$delCount);
			} else {
				return false;
			}
		} else {
			return true;//no messages to delete
		}
	}
	public function delete()
	{
		//this deletes the stream on the server including all messages
		$iObj	= $this->xInfo(false);
		if ($iObj !== false) {
			$this->terminate(true);
			
			$cmdStr		= $this->getParent()->getRawCmd("DEL", array($this->getKey()));
			$this->getParent()->mainSocketWrite($cmdStr);
			
			$rData		= $this->getParent()->mainSocketRead(true);
			if (preg_match("/(^\:[0-1]\r\n)$/si", $rData) === 1) {
				return $this; //0-1 because if the stream was deleted between the xInfo and del calls, it returns 0
			} elseif (strpos($rData, "-ERR") === 0) {
				throw new \Exception("Error: ".$rData);
			} else {
				throw new \Exception("Not handled for return: ".$rData);
			}
		}
	}
	public function terminate($throw=true)
	{
		$errObj	= null;
		foreach ($this->getGroups() as $grpObj) {
			try {
				$this->removeGroup($grpObj);
			} catch (\Exception $e) {
				if ($errObj === null) {
					$errObj	= $e;
				}
			}
		}
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