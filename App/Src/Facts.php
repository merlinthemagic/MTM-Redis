<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis;

class Facts
{
	//USE: $aFact		= \MTM\Redis\Facts::__METHOD__();
	
	protected static $_s=array();
	
	public static function getWorkers()
	{
		if (array_key_exists(__FUNCTION__, self::$_s) === false) {
			self::$_s[__FUNCTION__]	=	new \MTM\Redis\Factories\Workers();
		}
		return self::$_s[__FUNCTION__];
	}
	public static function getClients()
	{
		if (array_key_exists(__FUNCTION__, self::$_s) === false) {
			self::$_s[__FUNCTION__]	=	new \MTM\Redis\Factories\Clients();
		}
		return self::$_s[__FUNCTION__];
	}
	public static function getProcess()
	{
		if (array_key_exists(__FUNCTION__, self::$_s) === false) {
			self::$_s[__FUNCTION__]	=	new \MTM\Redis\Factories\Process();
		}
		return self::$_s[__FUNCTION__];
	}
	public static function getRedis()
	{
		if (array_key_exists(__FUNCTION__, self::$_s) === false) {
			self::$_s[__FUNCTION__]	=	new \MTM\Redis\Factories\Redis();
		}
		return self::$_s[__FUNCTION__];
	}
	public static function getMessages()
	{
		if (array_key_exists(__FUNCTION__, self::$_s) === false) {
			self::$_s[__FUNCTION__]	=	new \MTM\Redis\Factories\Messages();
		}
		return self::$_s[__FUNCTION__];
	}
	public static function getLogging()
	{
		if (array_key_exists(__FUNCTION__, self::$_s) === false) {
			self::$_s[__FUNCTION__]	=	new \MTM\Redis\Factories\Logging();
		}
		return self::$_s[__FUNCTION__];
	}
	public static function getHandlers()
	{
		if (array_key_exists(__FUNCTION__, self::$_s) === false) {
			self::$_s[__FUNCTION__]	=	new \MTM\Redis\Factories\Handlers();
		}
		return self::$_s[__FUNCTION__];
	}
}