<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

abstract class Base extends \MTM\RedisApi\Models\Clients\Base
{
	protected $_protocol=null;
	protected $_hostname=null;
	protected $_portNbr=null;
	protected $_authStr=null;
	protected $_timeout=30;
	protected $_sslCertObj=null;
	protected $_sslAllowSelfSigned=false;
	protected $_sslVerifyPeer=true;
	protected $_sslVerifyPeerName=true;
	protected $_chunkSize=4096;
	protected $_encoder="none";
	
	public function setConnection($protocol, $hostname, $portNbr, $auth=null, $timeout=30)
	{
		$this->_protocol	= $protocol;
		$this->_hostname	= $hostname;
		$this->_portNbr		= $portNbr;
		$this->_authStr		= $auth;
		$this->_timeout		= $timeout;
		return $this;
	}
	public function setSslConnection($certObj=null, $verifyPeer=true, $verifyPeerName=true, $allowSelfSigned=false)
	{
		if ($certObj !== null && $certObj instanceof \MTM\Certs\Models\CRT === false) {
			//should be a certificate object containing enough of the chain to confirm the server authenticity
			throw new \Exception("Invalid Certificate");
		} else {
			$this->_sslCertObj			= $certObj;
			$this->_sslVerifyPeer		= $verifyPeer;
			$this->_sslVerifyPeerName	= $verifyPeerName;
			$this->_sslAllowSelfSigned	= $allowSelfSigned;
		}
		return $this;
	}
	public function setDataEncoder($name)
	{
		if (in_array($name, array("php-serializer", "none")) === true) {
			$this->_encoder	= $name;
			return $this;
		} else {
			throw new \Exception("Invalid encoder: ". $name);
		}
	}
	public function getProtocol()
	{
		return $this->_protocol;
	}
	public function getHostname()
	{
		return $this->_hostname;
	}
	public function getPort()
	{
		return $this->_portNbr;
	}
	public function getAuth()
	{
		return $this->_authStr;
	}
	public function getTimeout()
	{
		return $this->_timeout;
	}
	public function getSslCert()
	{
		return $this->_sslCertObj;
	}
	public function getSslVerifyPeer()
	{
		return $this->_sslVerifyPeer;
	}
	public function getSslVerifyPeerName()
	{
		return $this->_sslVerifyPeerName;
	}
	public function getSslAllowSelfSigned()
	{
		return $this->_sslAllowSelfSigned;
	}
	public function getChunkSize()
	{
		return $this->_chunkSize;
	}
}