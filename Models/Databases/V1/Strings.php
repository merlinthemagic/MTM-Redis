<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

abstract class Strings extends Streams
{
	protected $_stringObjs=array();
	
	public function getStrings()
	{
		return array_values($this->_stringObjs);
	}
	public function getString($key)
	{
		//if not exist, add
		$strObj	= $this->getStringByKey($key, false);
		if ($strObj === null) {
			$strObj	= $this->addString($key);
		}
		return $strObj;
	}
	public function addString($key)
	{
		if ($this->getStringByKey($key, false) !== null) {
			throw new \Exception("String already exist: ".$key);
		}
		$strObj	= new \MTM\RedisApi\Models\Strings\V1\Zstance($this, $key);
		$this->_stringObjs[$strObj->getGuid()]	= $strObj;
		return $strObj;
	}
	public function removeString($strObj)
	{
		if (array_key_exists($strObj->getGuid(), $this->_stringObjs) === true) {
			unset($this->_stringObjs[$strObj->getGuid()]);
			$strObj->terminate();
			return $this;
		} else {
			throw new \Exception("String does not belong to this database");
		}
	}
	public function deleteString($strObj)
	{
		if (array_key_exists($strObj->getGuid(), $this->_stringObjs) === true) {
			//string is still usable, its just empty and will be created with the next set
			$this->delete($strObj->getKey())->exec(false);
			return $this;
		} else {
			throw new \Exception("String does not belong to this database");
		}
	}
	public function getStringByKey($key, $throw=false)
	{
		foreach ($this->_stringObjs as $strObj) {
			if ($strObj->getKey() == $key) {
				return $strObj;
			}
		}
		if ($throw === true) {
			throw new \Exception("String does not exist: ".$key);
		} else {
			return null;
		}
	}
}