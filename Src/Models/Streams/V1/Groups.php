<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Streams\V1;

abstract class Groups extends Cmds
{
	protected $_groupObjs=array();
	
	public function getGroups()
	{
		return array_values($this->_groupObjs);
	}
	public function addGroup($name)
	{
		if ($this->getGroupByName($name, false) !== null) {
			throw new \Exception("Group already exist: ".$name);
		}
		$grpObj		= new \MTM\RedisApi\Models\Groups\V1($this, $name);
		$this->getParent()->getPhpRedis()->xGroup("create", $this->getKey(), $name, 0, true);
		$this->_groupObjs[$grpObj->getGuid()]	= $grpObj;
		return $grpObj;
	}
	public function removeGroup($grpObj)
	{
		if (array_key_exists($grpObj->getGuid(), $this->_groupObjs) === true) {
			unset($this->_groupObjs[$grpObj->getGuid()]);
		} else {
			throw new \Exception("Group does not belong to this client");
		}
	}
	public function getGroupByName($name, $throw=false)
	{
		foreach ($this->_groupObjs as $grpObj) {
			if ($grpObj->getName() == $name) {
				return $chanObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("Group does not exist: ".$name);
		} else {
			return null;
		}
	}
}