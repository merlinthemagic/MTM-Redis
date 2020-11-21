<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Workers;

class V1 extends Base
{
	protected $_wsIp=null;
	protected $_wsPort=null;
	protected $_wsCert=null;
	protected $_sockObj=null;
	
	protected $_redisHost=null;
	protected $_redisPort=null;
	protected $_redisAuth=null;
	protected $_redisCert=null;
	protected $_redisTimeout=null;
	protected $_clientObjs=array();
	
	protected $_reqCb=null;
	protected $_initConnCb=null;
	protected $_termConnCb=null;

	public function setWssConfig($ip, $port, $certObj=null)
	{
		$this->_wsIp		= $ip;
		$this->_wsPort		= $port;
		$this->_wsCert		= $certObj;
		$this->setExceptionCb(\MTM\Redis\Facts::getLogging(), "exception");
		\MTM\Redis\Facts::getProcess()->addLoopCb($this, "pollClients");
		return $this;
	}
	public function setRedisConfig($host, $port=6379, $auth="", $certObj=null, $timeout=30)
	{
		$this->_redisHost		= $host;
		$this->_redisPort		= $port;
		$this->_redisAuth		= $auth;
		$this->_redisCert		= $certObj;
		$this->_redisTimeout	= $timeout;
		return $this;
	}
	public function setRequestCb($obj, $method)
	{
		$this->_reqCb	= array($obj, $method);
		return $this;
	}
	public function setInitClientCb($obj, $method)
	{
		$this->_initConnCb	= array($obj, $method);
		return $this;
	}
	public function setTermClientCb($obj, $method)
	{
		$this->_termConnCb	= array($obj, $method);
		return $this;
	}
	public function getClients()
	{
		return $this->_clientObjs;
	}
	public function pollClients()
	{
		$count	= 0;
		$this->getSocket()->getClients();
		foreach ($this->getClients() as $cObj) {
		
			try {
				
				$count		+= $cObj->getRedis()->pollSub();
				foreach ($cObj->getSocket()->getMessages() as $msg) {
					
					if ($msg == "GoodByeServer" || $msg == "") {
						//client is leaving / has left
						$this->setTerm();
						break;
					} else {
						$count++;
						$msgObj	= json_decode($msg);
						
						
						if ($msgObj !== false) {
							if (
								$msgObj instanceof \stdClass === true
								&& $msgObj->version === 1
								&& property_exists($msgObj, "type") === true
							) {
								if ($msgObj->type == "request") {
									
									$reqObj		= \MTM\Redis\Facts::getMessages()->getIngressV1($cObj);
									
									try {

										$reqObj->parseIngress($msgObj);
										
										if ($this->_reqCb !== null) {
											if (call_user_func_array($this->_reqCb, array($reqObj)) === true) {
												\MTM\Redis\Facts::getHandlers()->handle($reqObj);
											} else {
												throw new \Exception("Request was denied");
											}
										}

									} catch (\Exception $e) {
										$reqObj->setException($e)->send();
										$this->callException($e);
									}
										
								} elseif ($msgObj->type == "response") {
									
									$reqObj		= \MTM\Redis\Facts::getMessages()->getEgressById($msgObj->msgId, false);
									if ($reqObj !== null) {
										$reqObj->socketResp($msgObj);
									} else {
										//message is expired, need logging
									}
									
								} else {
									throw new \Exception("Not handled for message type: ".$msgObj->type);
								}
								
							} else {
								throw new \Exception("Invalid message received");
							}
									
						} else {
							throw new \Exception("Could not decode Json");
						}
					}
				}
			} catch (\Exception $e) {
				$this->callException($e);
			}
		}
		return $count;
	}
	protected function getSocket()
	{
		if ($this->_sockObj === null) {
			
			$sockObj		= \MTM\WsSocket\Factories::getSockets()->getNewServer();
			if ($this->_wsCert === null) {
				$sockObj->setConnection("tcp", $this->_wsIp, $this->_wsPort);
			} else {
				$sockObj->setConnection("tls", $this->_wsIp, $this->_wsPort);
				$sockObj->setSslConnection($this->_wsCert);
			}
			$sockObj->setClientDefaultMaxReadTime(1000);
			$sockObj->setClientDefaultMaxWriteTime(1000);
			$sockObj->setNewClientCb($this, "handleConnects");
			$sockObj->setClientTerminationCb($this, "handleTerminations");
			$this->_sockObj	= $sockObj;
		}
		return $this->_sockObj;
	}
	public function handleConnects($sockObj)
	{
		$uuid	= $sockObj->getUuid();
		if (array_key_exists($uuid, $this->_clientObjs) === false) {
			$redisObj	= \MTM\Redis\Facts::getRedis()->getClient($this->_redisHost, $this->_redisPort, $this->_redisAuth, $this->_redisCert, $this->_redisTimeout);
			$redisObj->setDataEncoder("php-serializer");
			$clientObj	= \MTM\Redis\Facts::getClients()->getV1($sockObj, $redisObj);
			
			try {
				
				if ($this->_initConnCb !== null) {
					if (call_user_func_array($this->_initConnCb, array($clientObj)) !== true) {
						throw new \Exception("Connection was denied");
					}
				}
				
				$this->_clientObjs[$uuid]	= $clientObj;
				
			} catch (\Exception $e) {
				$this->callException($e);
			}
		}
		return true;
	}
	public function handleTerminations($sockObj)
	{
		$uuid	= $sockObj->getUuid();
		if (array_key_exists($uuid, $this->_clientObjs) === true) {
			$clientObj	= $this->_clientObjs[$uuid];
			unset($this->_clientObjs[$uuid]);
			try {
				if ($this->_termConnCb !== null) {
					call_user_func_array($this->_termConnCb, array($clientObj));
				}
			} catch (\Exception $e) {
				$this->callException($e);
			}
			
			$clientObj->terminate(false);
		}
		return true;
	}
	public function terminate($throw=false)
	{
		if ($this->isTerm() === false) {
			parent::terminate($throw);
			if ($this->_sockObj !== null) {
				$this->_sockObj->terminate($throw);
			}
			$this->setTerm();
		}
		return $this;
	}
}