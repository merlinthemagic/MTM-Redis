<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Factories;

class Redis extends Base
{
	public function getClient($host, $port=6379, $auth="", $certObj=null, $timeout=30)
	{
		$hash	= hash("sha256", strtolower($host).$port);
		if (array_key_exists($hash, $this->_s) === false) {
			if (is_object($certObj) === true) {
				$clientObj		= \MTM\RedisApi\Facts::getClients()->getV1("tls", $host, $port, $auth, $timeout);
				$clientObj->setSslConnection($certObj, true, true, false);
			} else {
				$clientObj		= \MTM\RedisApi\Facts::getClients()->getV1("tcp", $host, $port, $auth, $timeout);
			}
			$this->_s[$hash]	= $clientObj;
		}
		return $this->_s[$hash];
	}
}