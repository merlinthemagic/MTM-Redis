<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Workers;

abstract class Base extends \DC\SCC\Extend\Base
{
	protected $_exCb=null;
	
	public function __construct()
	{
		register_shutdown_function(array($this, "__destruct"));
	}
	public function __destruct()
	{
		$this->terminate(false);
	}
	public function callException($e)
	{
		if ($this->_exCb !== null) {
			call_user_func_array($this->_exCb, array($e));
		}
		return $this;
	}
	public function setExceptionCb($obj, $method)
	{
		if (is_object($obj) === true && is_string($method) === true) {
			$this->_exCb		= array($obj, $method);
		}
		return $this;
	}
	public function terminate($throw=false)
	{
		return $this;
	}
}