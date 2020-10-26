<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets;

class V1 extends Base
{
	protected $_id=null;
	protected $_sockObj=null;
	protected $_isInit=false;
	protected $_isTerm=false;
	
	protected $_isTracked=false;
	protected $_trackNoLoop=true;
	protected $_trackMode="OPTIN";
	protected $_trackPrefixes=array();
	
	public function getId()
	{
		return $this->_id;
	}
	public function getSocket()
	{
		return $this->_sockObj;
	}
	public function ping($throw=false)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Ping($this);
		return $cmdObj->exec($throw);
	}
	public function write($strCmd)
	{
		$cmdParts	= str_split($strCmd, $this->getParent()->getChunkSize());
		foreach ($cmdParts as $cmdPart) {
			$written	= fwrite($this->getSocket(), $cmdPart);
			if (strlen($cmdPart) != $written) {
				throw new \Exception("Failed to write command");
			}
		}
		return $this;
	}
	public function read($throw=false, $timeout=5000)
	{
		$tTime		= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + ($timeout / 1000);
		$rData		= "";
		while(true) {
			$data 	= fgets($this->getSocket());
			if ($data != "") {
				$rData	.= $data;
			} elseif ($rData != "") {
				return $rData;
			} elseif ($tTime < \MTM\Utilities\Factories::getTime()->getMicroEpoch()) {
				if ($throw === true) {
					throw new \Exception("Read command timeout");
				} else {
					return null;
				}
			}
		}
	}
	public function initialize()
	{
		if ($this->_isInit === false) {

			if ($this->getParent()->getSslCert() === null) {
				$strConn	= $this->getParent()->getProtocol()."://".$this->getParent()->getHostname().":".$this->getParent()->getPort()."/";
			} else {
				//steal logic from wsSocket client
				throw new \Exception("Not yet handled for tls");
			}
			
			$sockRes 		= stream_socket_client($strConn, $errno, $errstr, $this->getParent()->getTimeout(), STREAM_CLIENT_CONNECT);
			if (is_resource($sockRes) === false) {
				throw new \Exception("Socket Error: " . $errstr, $errno);
			}
				
			stream_set_blocking($sockRes, false);
			stream_set_chunk_size($sockRes, $this->getParent()->getChunkSize());
			
			$this->_sockObj	= $sockRes;
			
			if ($this->getParent()->getAuth() != "") {
				
				try {
				
					$cmdObj		= new \MTM\RedisApi\Models\Cmds\Auth($this);
					$cmdObj->setAuth($this->getParent()->getAuth())->exec(true);

				} catch (\Exception $e) {
					fclose($this->_sockObj);
					$this->_sockObj	= null;
					throw $e;
				}
			}
			
			try {
				
				$cmdObj		= new \MTM\RedisApi\Models\Cmds\ClientId($this);
				$this->_id	= $cmdObj->exec(true);
				
			} catch (\Exception $e) {
				fclose($this->_sockObj);
				$this->_sockObj	= null;
				throw $e;
			}

			$this->_isInit	= true;
		}
		return $this;
	}
	public function enableTracking()
	{
		if ($this->isTracked() === false) {
			$subSock	= $this->getParent()->getSubSocket();
			if ($subSock->getId() === $this->getId()) {
				throw new \Exception("You cannot enable tracking on the the subscription socket");
			}
			
			$cmdObj		= new \MTM\RedisApi\Models\Cmds\ClientTracking($this);
			$cmdObj->setTrack(true)->setRedirectionId($subSock->getId());
			$cmdObj->setNoLoop($this->getTrackNoLoop())->setMode($this->getTrackMode());
			foreach ($this->getTrackPrefixes() as $prefix) {
				$cmdObj->addPrefix($prefix);
			}
			$cmdObj->exec(true);
			$this->_isTracked			= true;
		}
		return $this;
	}
	public function disableTracking()
	{
		if ($this->isTracked() === true) {
			
			$cmdObj				= new \MTM\RedisApi\Models\Cmds\ClientTracking($this);
			$cmdObj->setTrack(false)->exec(true);
			$this->_isTracked	= false;
		}
		return $this;
	}
	public function isTracked()
	{
		return $this->_isTracked;
	}
	public function getTrackPrefixes()
	{
		return $this->_trackPrefixes;
	}
	public function setTrackPrefixes($prefixes=array())
	{
		$this->_trackPrefixes	= $prefixes;
		return $this;
	}
	public function getTrackNoLoop()
	{
		return $this->_trackNoLoop;
	}
	public function setTrackNoLoop($bool=true)
	{
		$this->_trackNoLoop	= $bool;
		return $this;
	}
	public function getTrackMode()
	{
		return $this->_trackMode;
	}
	public function setTrackMode($mode="OPTIN")
	{
		$this->_trackMode	= $mode;
		return $this;
	}
	public function terminate($throw=false)
	{
		if ($this->_isInit === true && $this->_isTerm === false) {
			$this->read(false, -1); //clear the socket before quitting
			$cmdObj		= new \MTM\RedisApi\Models\Cmds\Quit($this);
			$cmdObj->exec($throw);
			
			fclose($this->_sockObj);
			$this->_isTerm	= true;
		}
		return $this;
	}
}