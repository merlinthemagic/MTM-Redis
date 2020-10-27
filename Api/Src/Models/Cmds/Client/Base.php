<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Base
{
	protected $_clientObj=null;

	public function __construct($clientObj)
	{
		$this->_clientObj	= $clientObj;
		parent::__construct();
	}
	public function getClient()
	{
		return $this->_clientObj;
	}
}