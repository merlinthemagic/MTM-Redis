<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Groups;

class V1 extends Base
{
	public function xReadNextUnconsumed($timeout=1, $throw=false)
	{
		$msgs	= $this->getClient()->xReadGroup($this->getName(), $this->getGuid(), [$this->getParent()->getKey() => ">"], 1, $timeout);
		if ($msgs !== false) {
			if (count($msgs) == 1) {
				$msgs				= reset($msgs);
				$msgId				= array_keys($msgs);
				$msgId				= reset($msgId);
				$msg				= reset($msgs);
				
				$msgObj				= $this->getMsgObj();
				$msgObj->id			= $msgId;
				$msgObj->payload	= $msg;
				return $msgObj;
			} elseif ($throw === true) {
				throw new \Exception("All messages have been consumed by group");
			} else {
				return null;
			}
		} elseif ($throw === true) {
			throw new \Exception("Reading group failed, likely stream was deleted");
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
		return $this->getParent()->xDelByMsgId($msgId, $throw);
	}
}