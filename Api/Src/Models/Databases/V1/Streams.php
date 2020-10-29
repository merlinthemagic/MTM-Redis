<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Streams extends Lists
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
		$streamObj		= new \MTM\RedisApi\Models\Streams\V1\Zstance($this, $key);
		$this->_streamObjs[$streamObj->getGuid()]	= $streamObj;
		return $streamObj;
	}
	public function removeStream($streamObj)
	{
		if (array_key_exists($streamObj->getGuid(), $this->_streamObjs) === true) {
			unset($this->_streamObjs[$streamObj->getGuid()]);
			$streamObj->terminate();
		} else {
			throw new \Exception("Stream does not belong to this database");
		}
	}
	public function deleteStream($streamObj)
	{
		if (array_key_exists($streamObj->getGuid(), $this->_streamObjs) === true) {
			//stream is not usable any longer
			$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Del\V1($this);
			$cmdObj->setKey($streamObj->getKey())->exec(false);
			$this->removeStream($streamObj);
			return $this;
		} else {
			throw new \Exception("Stream does not belong to this database");
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