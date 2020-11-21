<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Models\Clients\V1;

abstract class Cmds extends Authorization
{
	protected $_cmdCb=null;
	
	public function setCmdCb($obj, $method)
	{
		$this->_cmdCb	= array($obj, $method);
		return $this;
	}
	public function pingSocket()
	{
		$reqObj		= \MTM\Redis\Facts::getMessages()->getEgressV1($this);
		$reqObj->setL1("Sockets")->setL2("Get")->setL3("Ping");
		if ($this->_cmdCb !== null) {
			$reqObj->setRespCb($this->_cmdCb[0], $this->_cmdCb[1]);
		}
		return $reqObj->send();
	}
	public function setClientId($strId)
	{
		if (is_string($strId) === false) {
			throw new \Exception("Input is invalid");
		}
		$reqObj		= \MTM\Redis\Facts::getMessages()->getEgressV1($this);
		$reqObj->setL1("Clients")->setL2("Set")->setL3("Id");
		$reqObj->addReq("id", $strId);
		$reqObj->setRespCb($this, "setIdCb");
		if ($this->_cmdCb !== null) {
			$reqObj->setRespCb($this->_cmdCb[0], $this->_cmdCb[1]);
		}
		return $reqObj->send();
	}
	public function setAuthSecret($secret)
	{
		//Note: setAuthSecretOOB($secret) offers real security this function transmit the secret in band
		//Warning: a client may drop some requests that do not require response
		//because they are transmitted prior to the callback being executed and 
		//are therefore hashed with the old secret
		if (is_string($secret) === false) {
			//cannot use null, will cause hash mismatch between JS and PHP
			throw new \Exception("Input is invalid");
		}
		$reqObj		= \MTM\Redis\Facts::getMessages()->getEgressV1($this);
		$reqObj->setL1("Clients")->setL2("Set")->setL3("Secret");
		$reqObj->addReq("secret", $secret);
		$reqObj->setRespCb($this, "setSecretCb");
		if ($this->_cmdCb !== null) {
			$reqObj->setRespCb($this->_cmdCb[0], $this->_cmdCb[1]);
		}
		return $reqObj->send();
	}
	
	//command call backs
	public function setIdCb($reqObj)
	{
		if ($reqObj->getException() === null) {
			$this->_clientId	= $reqObj->getReq("id");
		}
	}
	public function setSecretCb($reqObj)
	{
		if ($reqObj->getException() === null) {
			$this->_authSecret	= $reqObj->getReq("secret");
		}
	}
}