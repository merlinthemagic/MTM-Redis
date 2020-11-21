<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands\Sockets;

class Cmds extends Get
{
	protected function ping($reqObj)
	{
		$reqObj->getClient()->getAuth(true, "PING");
		$sockObj	= $reqObj->getClient()->getRedis()->getMainSocket();
		$reqObj->setResp($sockObj->ping()->exec(true))->send();
	}
}