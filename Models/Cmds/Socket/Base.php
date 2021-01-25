<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Socket;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Base
{
	protected $_sockObj=null;

	public function __construct($sockObj)
	{
		$this->_sockObj	= $sockObj;
		parent::__construct();
	}
	public function getClient()
	{
		return $this->getSocket()->getClient();
	}
	public function getSocket()
	{
		return $this->_sockObj;
	}
}