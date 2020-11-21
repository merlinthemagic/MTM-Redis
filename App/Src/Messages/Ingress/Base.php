<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Messages\Ingress;

abstract class Base extends \MTM\Redis\Messages\Base
{
	protected $_recvTime=null;
	
	public function __construct()
	{
		parent::__construct();
		$this->_recvTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
	}
	public function setMsgId($id)
	{
		$this->_msgId	= $id;
		return $this;
	}
	public function getRecvTime()
	{
		return $this->_recvTime;
	}
}