<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands\Strings;

class Get extends Set
{
	protected function get($reqObj)
	{
		if ($reqObj->getReq("dbId") === null) {
			throw new \Exception("Database Id attribute is mandatory");
		} elseif ($reqObj->getReq("key") === null) {
			throw new \Exception("Database key attribute is mandatory");
		}
		$reqObj->getClient()->getAuth(true, "GET", $reqObj->getReq("dbId"), $reqObj->getReq("key"));
		
		$dbObj		= $reqObj->getClient()->getRedis()->getDatabase($reqObj->getReq("dbId"));
		$keyObj		= $dbObj->getString($reqObj->getReq("key"));
		$reqObj->setResp($keyObj->get()->exec(true))->send();
	}
}