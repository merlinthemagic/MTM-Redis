<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Channels;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_parentObj=null;
	protected $_name=null;
	
	public function __construct($clientObj, $name)
	{
		$this->_parentObj	= $clientObj;
		$this->_name		= $name;
		parent::__construct();
	}
	public function __destruct()
	{
		$this->unsubscribe();
	}
	public function getParent()
	{
		return $this->_parentObj;
	}
	public function getName()
	{
		return $this->_name;
	}
}