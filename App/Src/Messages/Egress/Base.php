<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Messages\Egress;

abstract class Base extends \MTM\Redis\Messages\Base
{
	protected $_respCb=array();

	public function __construct()
	{
		parent::__construct();
		$this->_msgId	= \MTM\Utilities\Factories::getGuids()->getV4()->get(false);
	}
	public function setRespCb($obj, $method)
	{
		$this->_respCb[]	= array($obj, $method);
		return $this;
	}
	public function respCb()
	{
		foreach ($this->_respCb as $cb) {
			try {
				call_user_func_array($cb, array($this));
			} catch (\Exception $e) {
			}
		}
		return $this;
	}
}