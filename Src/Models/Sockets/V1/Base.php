<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets\V1;

abstract class Base extends \MTM\RedisApi\Models\Sockets\Base
{
	protected $_clientObj=null;
	protected $_isSub=false;
	
	public function __construct($clientObj, $isSub=false)
	{
		$this->_clientObj	= $clientObj;
		$this->_isSub		= $isSub;
		parent::__construct();
	}
	public function __destruct()
	{
		//dont want to throw in shutdown
		$this->terminate(false);
	}
	public function getClient()
	{
		return $this->_clientObj;
	}
}