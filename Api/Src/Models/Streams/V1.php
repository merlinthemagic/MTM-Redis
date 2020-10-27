<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Streams;

class V1 extends Base
{
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
			if (preg_match("/^(\:[0-1]\r\n)$/si", $rData) === 1) {
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