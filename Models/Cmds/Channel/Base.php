<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Channel;

abstract class Base extends \MTM\RedisApi\Models\Cmds\Base
{
	protected $_chanObj=null;

	public function __construct($channelObj)
	{
		$this->_chanObj	= $channelObj;
		parent::__construct();
	}
	public function getChannel()
	{
		return $this->_chanObj;
	}
	public function getClient()
	{
		return $this->getChannel()->getClient();
	}
}