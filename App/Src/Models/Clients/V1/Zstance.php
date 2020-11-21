<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Models\Clients\V1;

class Zstance extends Tracking
{
	protected $_sockObj=null;
	protected $_redisObj=null;
	
	public function setSocket($sockObj)
	{
		$this->_sockObj		= $sockObj;
		return $this;
	}
	public function getSocket()
	{
		return $this->_sockObj;
	}
	public function setRedis($redisObj)
	{
		$this->_redisObj	= $redisObj;
		return $this;
	}
	public function getRedis()
	{
		return $this->_redisObj;
	}
	public function terminate($throw=false)
	{
		foreach ($this->_keyObjs as $keyObj) {
			$this->unTrackKey($keyObj);
		}
		if ($this->getSocket() !== null) {
			$this->getSocket()->terminate(false);
		}
		if ($this->getRedis() !== null) {
			$this->getRedis()->terminate(false);
		}
	}
}