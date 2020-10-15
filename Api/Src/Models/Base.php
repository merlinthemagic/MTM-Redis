<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models;

abstract class Base
{
	protected $_guid=null;
	
	public function __construct()
	{
		$this->_guid	=  \MTM\Utilities\Factories::getGuids()->getV4()->get(false);
	}
	public function getGuid()
	{
		return $this->_guid;
	}
}