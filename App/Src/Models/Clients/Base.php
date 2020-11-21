<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Models\Clients;

abstract class Base extends \MTM\Redis\Models\Base
{
	protected $_guid=null;
	
	public function __construct()
	{
		$this->_guid	= \MTM\Utilities\Factories::getGuids()->getV4()->get(false);
	}
	public function getGuid()
	{
		return $this->_guid;
	}
}