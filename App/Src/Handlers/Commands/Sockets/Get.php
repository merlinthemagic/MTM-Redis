<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands\Sockets;

class Get extends Set
{
	protected function getClientId($reqObj)
	{
		$sockObj	= $reqObj->getClient()->getRedis()->getMainSocket();
		$reqObj->setResp($sockObj->getId())->send();
	}
}