<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers;

abstract class Base
{
	protected $_s=array();
	
	protected function routeInvalid($reqObj)
	{
		\MTM\Redis\Facts::getHandlers()->routeInvalid($reqObj);
	}
}