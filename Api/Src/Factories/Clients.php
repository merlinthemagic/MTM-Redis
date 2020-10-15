<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Factories;

class Clients extends Base
{
	public function getV1($protocol="tcp", $host="127.0.0.1", $port=6379, $auth=null, $timeout=30)
	{
		$rObj	= new \MTM\RedisApi\Models\Clients\V1();
		$rObj->setConnection($protocol, $host, $port, $auth, $timeout);
		return $rObj;
	}
}