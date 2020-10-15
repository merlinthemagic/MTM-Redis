<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Channels;

//pattern based channel

class V2 extends Base
{
	protected $_isSub=false;
	protected $_msgs=array();

	public function addMsg($channel, $payload)
	{
		//called only from parent
		$msgObj				= $this->getMsgObj();
		$msgObj->payload	= $payload;
		$msgObj->channel	= $channel;
		$this->_msgs[]		= $msgObj;
		return $this;
	}
	public function isSubscribed()
	{
		return $this->_isSub;
	}
	public function subscribe()
	{
		if ($this->_isSub === false) {
			
			$cmdStr		= "*2\r\n\$10\r\nPSUBSCRIBE\r\n\$".strlen($this->getName())."\r\n".$this->getName()."\r\n";
			$this->getParent()->socketWrite($this->getParent()->getChanSocket(), $cmdStr);

			$rData		= $this->getParent()->chanSocketRead(true);
			$regEx		= "/^\*3\r\n\\\$10\r\nPSUBSCRIBE\r\n\\\$".strlen($this->getName())."\r\n".$this->getRegExName()."\r\n\:([0-9]+)\r\n$/si";
			if (preg_match($regEx, $rData, $raw) === 1) {
				$this->_isSub	= true;
				return intval($raw[1]); //number of subscribers total
			} elseif (strpos($rData, "-ERR") === 0) {
				throw new \Exception("Error: ".$rData);
			} else {
				throw new \Exception("Not handled for return: ".$rData);
			}
		}
		return $this;
	}
	public function unsubscribe()
	{
		if ($this->_isSub === true) {
			$cmdStr		= "*2\r\n\$12\r\nPUNSUBSCRIBE\r\n\$".strlen($this->getName())."\r\n".$this->getName()."\r\n";
			$this->getParent()->socketWrite($this->getParent()->getChanSocket(), $cmdStr);
			$rData		= $this->getParent()->chanSocketRead(true);
			$regEx		= "/^\*3\r\n\\\$12\r\nPUNSUBSCRIBE\r\n\\\$".strlen($this->getName())."\r\n".$this->getRegExName()."\r\n\:([0-9]+)\r\n$/si";
			if (preg_match($regEx, $rData, $raw) === 1) {
				$this->_isSub	= false;
				return intval($raw[1]); //number of subscribers remaining
			} elseif (strpos($rData, "-ERR") === 0) {
				throw new \Exception("Error: ".$rData);
			} else {
				throw new \Exception("Not handled for return: ".$rData);
			}
		}
		return $this;
	}
	public function getMessages($count=-1, $timeout=1000)
	{
		$max	= count($this->_msgs);
		if ($count < 0 || $max < $count) {
			$this->getParent()->chanSocketRead(false, $timeout); //fetch new messages
			$max	= count($this->_msgs); //new max count
		}
		if ($count < 0 || $count > $max) {
			$count	= $max; //get all
		}
		$rMsgs	= array();
		$i		= 0;
		foreach($this->_msgs as $mId => $msgObj) {
			$i++;
			$rMsgs[]	= $msgObj;
			unset($this->_msgs[$mId]);
			if ($count == $i) {
				break;
			}
		}
		return $rMsgs;
	}
	public function getMessage($timeout=1000)
	{
		$rMsgs	= $this->getMessages(1, $timeout);
		if (count($rMsgs) > 0) {
			return reset($rMsgs);
		} else {
			return null;
		}
	}
}