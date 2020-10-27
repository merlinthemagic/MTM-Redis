<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

abstract class Channels extends Base
{
	protected $_chanObjs=array();
	
	public function getChannels()
	{
		return array_values($this->_chanObjs);
	}
	public function getChannel($name)
	{
		//if not exist, add
		$chanObj	= $this->getChannelByName($name, false);
		if ($chanObj === null) {
			$chanObj	= $this->addChannel($name);
		}
		return $chanObj;
	}
	public function addChannel($name)
	{
		if ($this->getChannelByName($name, false) !== null) {
			throw new \Exception("Channel already exist: ".$name);
		}
		//remember to call subscribe()
		$chObj	= new \MTM\RedisApi\Models\Channels\V1($this, $name);
		$this->_chanObjs[$chObj->getGuid()]	= $chObj;
		return $chObj;
	}
	public function addPatternChannel($name)
	{
		if ($this->getChannelByName($name, false) !== null) {
			throw new \Exception("Channel already exist: ".$name);
		}
		//remember to call subscribe()
		$chObj	= new \MTM\RedisApi\Models\Channels\V2($this, $name);
		$this->_chanObjs[$chObj->getGuid()]	= $chObj;
		return $chObj;
	}
	public function removeChannel($chanObj)
	{
		if (array_key_exists($chanObj->getGuid(), $this->_chanObjs) === true) {
			unset($this->_chanObjs[$chanObj->getGuid()]);
			$chanObj->unsubscribe();
		} else {
			throw new \Exception("Channel does not belong to this client");
		}
	}
	public function getChannelByName($name, $throw=false)
	{
		foreach ($this->_chanObjs as $chanObj) {
			if ($chanObj->getName() == $name) {
				return $chanObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("Channel does not exist: ".$name);
		} else {
			return null;
		}
	}
}