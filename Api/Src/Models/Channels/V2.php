<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Channels;

//pattern based channel

class V2 extends Base
{
	public function addMsg($channel, $payload)
	{
		//called only from parent
		$msgObj				= $this->getMsgObj();
		$msgObj->payload	= $payload;
		$msgObj->channel	= $channel;
		if ($this->_cbs === null) {
			$this->_msgs[]		= $msgObj;
		} else {
			$this->exeCb($msgObj);
		}
		return $this;
	}
	public function isSubscribed()
	{
		return $this->_isSub;
	}
	public function subscribe()
	{
		if ($this->_isSub === false) {
			$cmdObj			= new \MTM\RedisApi\Models\Cmds\Psubscribe($this);
			$cmdObj->setPattern($this->getName());
			$cmdObj->exec(true);
			$this->_isSub	= true;
		}
		return $this;
	}
	public function unsubscribe()
	{
		if ($this->_isSub === true) {
			
			$cmdObj			= new \MTM\RedisApi\Models\Cmds\Punsubscribe($this);
			$cmdObj->setPattern($this->getName());
			$cmdObj->exec(true);
			$this->_isSub	= false;
		}
		return $this;
	}
}