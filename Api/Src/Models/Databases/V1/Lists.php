<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Lists extends Base
{
	protected $_listObjs=array();
	
	public function getLists()
	{
		return array_values($this->_listObjs);
	}
	public function getList($key)
	{
		//if not exist, add
		$listObj	= $this->getListByKey($key, false);
		if ($listObj === null) {
			$listObj	= $this->addList($key);
		}
		return $listObj;
	}
	public function addList($key)
	{
		if ($this->getListByKey($key, false) !== null) {
			throw new \Exception("List already exist: ".$key);
		}
		$listObj	= new \MTM\RedisApi\Models\Lists\V1\Zstance($this, $key);
		$this->_listObjs[$listObj->getGuid()]	= $listObj;
		return $listObj;
	}
	public function removeList($listObj)
	{
		if (array_key_exists($listObj->getGuid(), $this->_listObjs) === true) {
			unset($this->_listObjs[$listObj->getGuid()]);
			$listObj->terminate();
		} else {
			throw new \Exception("List does not belong to this database");
		}
	}
	public function getListByKey($key, $throw=false)
	{
		foreach ($this->_listObjs as $listObj) {
			if ($listObj->getKey() == $key) {
				return $listObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("List does not exist: ".$key);
		} else {
			return null;
		}
	}
}