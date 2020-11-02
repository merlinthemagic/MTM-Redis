<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

abstract class Sockets extends Lists
{
	protected $_chMsgCount=0;
	protected $_mainSockObj=null;
	protected $_subSockObj=null;
	
	public function getMainSocket()
	{
		if ($this->_mainSockObj === null) {
			$sockObj			= new \MTM\RedisApi\Models\Sockets\V1\Zstance($this);
			$this->_mainSockObj	= $sockObj->initialize();
		}
		return $this->_mainSockObj;
	}
	public function getSubSocket()
	{
		if ($this->_subSockObj === null) {
			$sockObj			= new \MTM\RedisApi\Models\Sockets\V1\Zstance($this);
			$this->_subSockObj	= $sockObj->initialize();
		}
		return $this->_subSockObj;
	}
	public function pollSub()
	{
		$curCount	= $this->_chMsgCount;
		$this->subSocketRead(false, -1);
		return $this->_chMsgCount - $curCount; //return number of new messages, helpful to throttle poll frequency
	}
	public function subSocketRead($throw=false, $timeout=5000)
	{
		$sTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		$rData	= $this->getSubSocket()->read($throw, $timeout);
		$eTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		$rTime	= $timeout - (($eTime - $sTime) * 1000);
		if (strpos($rData, "message\r\n") !== false) {
			
			$head	= "*3\r\n$7\r\nmessage\r\n";
			$hLen	= strlen($head);
			while(true) {
				$sPos	= strpos($rData, $head);
				if ($sPos !== false) {
					
					$reData		= $rData;
					$rData		= substr($rData, 0, $sPos);
					$reData		= substr($reData, ($sPos + $hLen));
					
					$nPos		= strpos($reData, "\r\n");
					$chanLen	= intval(substr($reData, 1, $nPos));
					$chanName	= substr($reData, ($nPos + 2), $chanLen);
					$reData		= substr($reData, ($nPos + $chanLen + 4));
					
					if (preg_match("/^(|__redis__)/", $chanName) === 0) {
						$nPos		= strpos($reData, "\r\n");
						$payLen		= intval(substr($reData, 1, $nPos));
						$payload	= substr($reData, ($nPos + 2), $payLen);
						$rData		.= substr($reData, ($nPos + $payLen + 4));
					} else {
						$nPos		= strpos($reData, "\r\n");
						$keyCount	= intval(substr($reData, 1, $nPos));
						if ($keyCount < 0) {
							$payload	= "FLUSHALL";
							$reData		= substr($reData, ($nPos+2));
						} else {
							$payload	= array();
							$reData		= substr($reData, ($nPos+2));
							for ($x=0; $x < $keyCount; $x++) {
								$nPos		= strpos($reData, "\r\n");
								$keyLen		= intval(substr($reData, 1, $nPos));
								$payload[]	= substr($reData, ($nPos + 2), $keyLen);
								$reData		= substr($reData, ($nPos + $keyLen + 4));
							}
						}
						
						$rData		.= $reData;
					}
					
					$chanObj	= $this->getChannelByName($chanName, false);
					if ($chanObj !== null) {
						if (preg_match("/^(__keyevent|__keyspace|__redis__)/", $chanName) === 0) {
							//TODO: investigate if clients can signal the default serializer used to redis
							//so all messages are serialized the same, this is not exactly sustainable
							$payload	= $this->dataDecode($payload);
						}
						$chanObj->addMsg($payload);
						$this->_chMsgCount++;
					}
					
				} else {
					break;
				}
			}
			$head	= "*4\r\n$8\r\npmessage\r\n";
			$hLen	= strlen($head);
			while(true) {
				$sPos	= strpos($rData, $head);
				if ($sPos !== false) {
					
					$reData		= $rData;
					$rData		= substr($rData, 0, $sPos);
					$reData		= substr($reData, ($sPos + $hLen));
					
					$nPos		= strpos($reData, "\r\n");
					$patternLen	= intval(substr($reData, 1, $nPos));
					$pattern	= substr($reData, ($nPos + 2), $patternLen);
					$reData		= substr($reData, ($nPos + $patternLen + 4));
					
					$nPos		= strpos($reData, "\r\n");
					$chanLen	= intval(substr($reData, 1, $nPos));
					$chanName	= substr($reData, ($nPos + 2), $chanLen);
					$reData		= substr($reData, ($nPos + $chanLen + 4));
					
					if (preg_match("/^(|__redis__)/", $chanName) === 0) {
						$nPos		= strpos($reData, "\r\n");
						$payLen		= intval(substr($reData, 1, $nPos));
						$payload	= substr($reData, ($nPos + 2), $payLen);
						$rData		.= substr($reData, ($nPos + $payLen + 4));
					} else {
						$payload	= array();
						$nPos		= strpos($reData, "\r\n");
						$keyCount	= intval(substr($reData, 1, $nPos));
						$reData		= substr($reData, ($nPos+2));
						for ($x=0; $x < $keyCount; $x++) {
							$nPos		= strpos($reData, "\r\n");
							$keyLen		= intval(substr($reData, 1, $nPos));
							$payload[]	= substr($reData, ($nPos + 2), $keyLen);
							$reData		= substr($reData, ($nPos + $keyLen + 4));
						}
						$rData		.= $reData;
					}
					
					$chanObj	= $this->getChannelByName($pattern, false);
					if ($chanObj !== null) {
						if (preg_match("/^(__keyevent|__keyspace|__redis__)/", $chanName) === 0) {
							//TODO: investigate if clients can signal the default serializer used to redis
							//so all messages are serialized the same, this is not exactly sustainable
							$payload	= $this->dataDecode($payload);
						}
						$chanObj->addMsg($chanName, $payload);
						$this->_chMsgCount++;
					}
					
				} else {
					break;
				}
			}
		}
		if ($rData == "" && $rTime > 0) {
			//we got a message, but no real data,
			//this will not end up nesting too deep, pub / sub messages are read many at a time
			//its only if we trigger a subscribe/unsubscribe and there is a pub pending that we recurse
			return $this->chanSocketRead($throw, $rTime);
		} else {
			return $rData;
		}
	}
}