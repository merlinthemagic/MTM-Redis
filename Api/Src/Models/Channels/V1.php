<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Channels;

class V1 extends Base
{
	protected $_isSub=false;
	protected $_msgs=array();

	public function addMsg($payload)
	{
		//called only from parent
		$msgObj				= $this->getMsgObj();
		$msgObj->payload	= $payload;
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
			
			$cmdStr		= "*2\r\n\$9\r\nSUBSCRIBE\r\n\$".strlen($this->getName())."\r\n".$this->getName()."\r\n";
			$this->getParent()->socketWrite($this->getParent()->getChanSocket(), $cmdStr);

			$rData		= $this->getParent()->chanSocketRead(true);
			$regEx		= "/^\*3\r\n\\\$9\r\nSUBSCRIBE\r\n\\\$".strlen($this->getName())."\r\n".$this->getRegExName()."\r\n\:([0-9]+)\r\n$/si";
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
			$cmdStr		= "*2\r\n\$11\r\nUNSUBSCRIBE\r\n\$".strlen($this->getName())."\r\n".$this->getName()."\r\n";
			$this->getParent()->socketWrite($this->getParent()->getChanSocket(), $cmdStr);
			$rData		= $this->getParent()->chanSocketRead(true);
			$regEx		= "/^\*3\r\n\\\$11\r\nUNSUBSCRIBE\r\n\\\$".strlen($this->getName())."\r\n".$this->getRegExName()."\r\n\:([0-9]+)\r\n$/si";
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
	public function setMessage($data, $ignoreDub=false)
	{
		//there is no requirement the channel be subscribed in order to publish
		$sCount		= count($this->_msgs);
		$cmdStr		= "*3\r\n\$7\r\nPUBLISH\r\n\$".strlen($this->getName())."\r\n".$this->getName()."\r\n$".strlen($data)."\r\n".$data."\r\n";
		$this->getParent()->socketWrite($this->getParent()->getMainSocket(), $cmdStr);
		
		$rData		= $this->getParent()->mainSocketRead(true);
		if (preg_match("/^\:([0-9]+)\r\n$/si", $rData, $raw) === 1) {
			
			$subCount	= intval($raw[1]);
			if ($ignoreDub === false && $this->isSubscribed() === true) {
				//because the message is published using the main socket
				//if we are subscribed (using the channel socket) we get a copy
				//lets get rid of that message
				$this->getParent()->chanSocketRead(false);
				
				$found	= false;
				$nMsgs	= array_slice($this->_msgs, $sCount, null, true);
				foreach (array_reverse($nMsgs, true) as $mId => $msgObj) {
					if ($msgObj->payload == $data) {
						unset($this->_msgs[$mId]);
						$found	= true;
						$subCount--;
						break;
					}
				}
				if ($found === false) {
					throw new \Exception("We failed to get a copy of our message even though we are subscribed");
				}
			}
			return $subCount; //number of subscribers the data was delivered to
			
		} elseif (strpos($rData, "-ERR") === 0) {
			throw new \Exception("Error: ".$rData);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
	}
	public function getMessages($count=-1, $timeout=1)
	{
		if ($timeout > 0) {
			$this->getParent()->chanSocketRead(false, $timeout); //fetch new messages
		}
		$max	= count($this->_msgs); //max count
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