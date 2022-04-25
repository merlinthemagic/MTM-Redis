<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket\Client\Tracking;

class V1 extends Base
{
	protected $_track=null;
	protected $_redirId=null;
	protected $_mode="OPTIN";
	protected $_noLoop=true;
	protected $_prefixes=array();
	
	public function setTrack($bool)
	{
		if (is_bool($bool) === false) {
			throw new \Exception("Invalid input");
		}
		$this->_track		= $bool;
		return $this;
	}
	public function getTrack()
	{
		return $this->_track;
	}
	public function setNoLoop($bool)
	{
		if (is_bool($bool) === false) {
			throw new \Exception("Invalid input");
		}
		$this->_noLoop		= $bool;
		return $this;
	}
	public function getNoLoop()
	{
		return $this->_noLoop;
	}
	public function setRedirectionId($input)
	{
		if ($input instanceof \MTM\RedisApi\Models\Sockets\V1) {
			$this->_redirId		= $input->getId();
		} elseif (is_int($input) === true) {
			$this->_redirId		= $input;
		} else {
			throw new \Exception("Invalid input");
		}
		return $this;
	}
	public function getRedirectionId()
	{
		return $this->_redirId;
	}
	public function setMode($mode)
	{
		$mode	= strtoupper($mode);
		if (in_array($mode, array("OPTIN", "OPTOUT", "BCAST")) === false) {
			throw new \Exception("Invalid input mode");
		}
		$this->_mode	= $mode;
		return $this;
	}
	public function getMode()
	{
		return $this->_mode;
	}
	public function getPrefixes()
	{
		return $this->_prefixes;
	}
	public function addPrefix($input)
	{
		if (is_string($input) === false && is_int($input) === false) {
			throw new \Exception("Invalid input, must be string or int");
		}
		$this->_prefixes[]	= $input;
		return $this;
	}
	public function getRawCmd()
	{
		$args	= array($this->getClientCmd());
		if ($this->getTrack() === true) {
			$args[]	= "ON";
			if ($this->getRedirectionId() !== null) {
				$args[]	= "REDIRECT";
				$args[]	= $this->getRedirectionId();
			}
			$args[]	= $this->getMode();
			if ($this->getNoLoop() === true) {
				$args[]	= "NOLOOP";
			}
			foreach ($this->getPrefixes() as $prefix) {
				$args[]	= "PREFIX";
				$args[]	= $prefix.":";
			}
		} else {
			$args[]	= "OFF";
		}
		return $this->getClient()->getRawCmd($this->getBaseCmd(), $args);
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->parse($this->getSocket()->write($this->getRawCmd())->read(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		$rVal	= $this->getClient()->parseResponse($rData);
		if ($rVal === "OK") {
			$this->setResponse($rVal);
		} elseif ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}