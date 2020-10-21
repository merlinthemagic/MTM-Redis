<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Channels;

class V1 extends Base
{
	protected $_iopls=false;
	
	public function addMsg($payload)
	{
		//called only from parent
		if (count($this->_iopls) > 0) {
			foreach ($this->_iopls as $i => $msg) {
				if ($payload === $msg) {
					//found a duplicate message
					unset($this->_iopls[$i]);
					return $this;
				}
			}
		}

		$msgObj				= $this->getMsgObj();
		$msgObj->payload	= $payload;
		if ($this->exeCb($msgObj) === false) {
			$this->_msgs[]		= $msgObj;
		}
		return $this;
	}
	public function addIgnoreOncePayload($payload)
	{
		//called only from publish cmd
		//because the message is published using the main socket
		//if we are subscribed (using the channel socket) we get a copy
		//lets get rid of those duplicate messages
		$this->_iopls[]		= $payload;
		return $this;
	}
	public function isSubscribed()
	{
		return $this->_isSub;
	}
	public function subscribe()
	{
		if ($this->_isSub === false) {
			$cmdObj			= new \MTM\RedisApi\Models\Cmds\Subscribe($this);
			$cmdObj->setName($this->getName());
			$cmdObj->exec(true);
			$this->_isSub	= true;
		}
		return $this;
	}
	public function unsubscribe()
	{
		if ($this->_isSub === true) {
			
			$cmdObj			= new \MTM\RedisApi\Models\Cmds\Unsubscribe($this);
			$cmdObj->setName($this->getName());
			$cmdObj->exec(true);
			$this->_isSub	= false;
		}
		return $this;
	}
	public function publish($msg)
	{
		//there is no requirement the channel be subscribed in order to publish
		$cmdObj			= new \MTM\RedisApi\Models\Cmds\Publish($this);
		$cmdObj->setMessage($msg);
		return $cmdObj;
	}
}