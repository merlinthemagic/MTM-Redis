<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets\V1;

abstract class Tracking extends Cmds
{
	protected $_isTracked=false;
	protected $_trackNoLoop=true;
	protected $_trackMode="OPTIN";
	protected $_trackPrefixes=array();
	
	public function enableTracking()
	{
		if ($this->isTracked() === false) {
			$subSock	= $this->getClient()->getSubSocket();
			if ($subSock->getId() === $this->getId()) {
				throw new \Exception("You cannot enable tracking on the the subscription socket");
			}
			
			$cmdObj		= $this->clientTracking(true)->setRedirectionId($subSock->getId());
			$cmdObj->setNoLoop($this->getTrackNoLoop())->setMode($this->getTrackMode());
			foreach ($this->getTrackPrefixes() as $prefix) {
				$cmdObj->addPrefix($prefix);
			}
			$cmdObj->exec(true);
			$this->_isTracked	= true;
		}
		return $this;
	}
	public function disableTracking()
	{
		if ($this->isTracked() === true) {
			$this->clientTracking(false)->exec(true);
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
}