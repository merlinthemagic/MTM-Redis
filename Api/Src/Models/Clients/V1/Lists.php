<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

abstract class Lists extends Databases
{
	protected $_listObjs=array();
	
	public function getLists()
	{
		return array_values($this->_listObjs);
	}
	public function getList($name)
	{
		//if not exist, add
		$listObj	= $this->getListByName($name, false);
		if ($listObj === null) {
			$listObj	= $this->addList($name);
		}
		return $listObj;
	}
	public function addList($name)
	{
		if ($this->getListByName($key, false) !== null) {
			throw new \Exception("List already exist: ".$name);
		}
		$listObj		= new \MTM\RedisApi\Models\Lists\V1($this, $name);
		$this->_listObjs[$listObj->getGuid()]	= $listObj;
		return $listObj;
	}
	public function removeList($listObj)
	{
		if (array_key_exists($listObj->getGuid(), $this->_listObjs) === true) {
			unset($this->_listObjs[$listObj->getGuid()]);
			$listObj->terminate();
		} else {
			throw new \Exception("List does not belong to this client");
		}
	}
	public function getListByName($name, $throw=false)
	{
		foreach ($this->_listObjs as $listObj) {
			if ($listObj->getName() == $name) {
				return $listObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("List name does not exist: ".$name);
		} else {
			return null;
		}
	}
}