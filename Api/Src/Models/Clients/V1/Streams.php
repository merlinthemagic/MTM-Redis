<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

abstract class Streams extends Sockets
{
	protected $_streamObjs=array();
	
	public function getStreams()
	{
		return array_values($this->_streamObjs);
	}
	public function getStream($key)
	{
		//if not exist, add
		$streamObj	= $this->getStreamByKey($key, false);
		if ($streamObj === null) {
			$streamObj	= $this->addStream($key);
		}
		return $streamObj;
	}
	public function addStream($key)
	{
		if ($this->getStreamByKey($key, false) !== null) {
			throw new \Exception("Stream already exist: ".$key);
		}
		$streamObj		= new \MTM\RedisApi\Models\Streams\V1($this, $key);
		$this->_streamObjs[$streamObj->getGuid()]	= $streamObj;
		return $streamObj;
	}
	public function removeStream($streamObj)
	{
		if (array_key_exists($streamObj->getGuid(), $this->_streamObjs) === true) {
			unset($this->_streamObjs[$streamObj->getGuid()]);
			$streamObj->terminate();
		} else {
			throw new \Exception("Stream does not belong to this client");
		}
	}
	public function getStreamByKey($key, $throw=false)
	{
		foreach ($this->_streamObjs as $streamObj) {
			if ($streamObj->getKey() == $key) {
				return $streamObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("Stream key does not exist: ".$key);
		} else {
			return null;
		}
	}
}