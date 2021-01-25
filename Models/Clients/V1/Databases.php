<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

abstract class Databases extends Cmds
{
	protected $_dbObjs=array();
	protected $_dbCount=null;
	
	public function getDatabases()
	{
		return array_values($this->_dbObjs);
	}
	public function getDatabase($id)
	{
		//if not exist, add
		$dbObj	= $this->getDatabaseById($id, false);
		if ($dbObj === null) {
			$dbObj	= $this->addDatabase($id);
		}
		return $dbObj;
	}
	public function addDatabase($id)
	{
		if ($this->getDatabaseById($id, false) !== null) {
			throw new \Exception("Database already exist: ".$id);
		}
		$dbObj	= new \MTM\RedisApi\Models\Databases\V1\Zstance($this, $id);
		$this->_dbObjs[$dbObj->getGuid()]	= $dbObj;
		return $dbObj;
	}
	public function removeDatabase($dbObj)
	{
		if (array_key_exists($dbObj->getGuid(), $this->_dbObjs) === true) {
			unset($this->_dbObjs[$dbObj->getGuid()]);
			$dbObj->terminate();
		} else {
			throw new \Exception("Database does not belong to this client");
		}
	}
	public function getDatabaseById($id, $throw=false)
	{
		foreach ($this->_dbObjs as $dbObj) {
			if ($dbObj->getId() == $id) {
				return $dbObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("Database does not exist: ".$id);
		} else {
			return null;
		}
	}
	public function getDatabaseCount()
	{
		if ($this->_dbCount === null) {
			//can cache because it requires a restart to change
			$this->_dbCount	= $this->newConfigGetDatabases()->exec(true);
		}
		return $this->_dbCount;
	}
}