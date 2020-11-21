<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands\Sockets;

class Get extends Set
{
	protected function getClientId($reqObj)
	{
		$reqObj->getClient()->getAuth(true, "CLIENT ID");
		$sockObj	= $reqObj->getClient()->getRedis()->getMainSocket();
		$reqObj->setResp($sockObj->getId())->send();
	}
	protected function getPing($reqObj)
	{
		$reqObj->getClient()->getAuth(true, "PING");
		$sockObj	= $reqObj->getClient()->getRedis()->getMainSocket();
		$reqObj->setResp($sockObj->ping()->exec(true))->send();
	}
}