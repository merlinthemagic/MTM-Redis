<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi;

class Facts
{
	//USE: $aFact		= \MTM\RedisApi\Facts::__METHOD_();
	
	protected static $_s=array();
	
	public static function getClients()
	{
		if (array_key_exists(__FUNCTION__, self::$_s) === false) {
			self::$_s[__FUNCTION__]	=	new \MTM\RedisApi\Factories\Clients();
		}
		return self::$_s[__FUNCTION__];
	}
}