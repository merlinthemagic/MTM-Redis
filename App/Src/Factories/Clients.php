<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Factories;

class Clients extends Base
{
	public function getV1($sockObj=null, $redisObj=null)
	{
		$cObj	= new \MTM\Redis\Models\Clients\V1\Zstance();
		$cObj->setSocket($sockObj)->setRedis($redisObj);
		return $cObj;
	}
}