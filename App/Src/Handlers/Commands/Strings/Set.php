<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands\Strings;

class Set extends \MTM\Redis\Handlers\Base
{
	protected function setCachingEnabled($reqObj)
	{
		if ($reqObj->getReq("dbId") === null) {
			throw new \Exception("Database Id attribute is mandatory");
		} elseif ($reqObj->getReq("key") === null) {
			throw new \Exception("Database key attribute is mandatory");
		}
		$reqObj->getClient()->getAuth(true, "CLIENT CACHING", $reqObj->getReq("dbId"), $reqObj->getReq("key"));
		$dbObj		= $reqObj->getClient()->getRedis()->getDatabase($reqObj->getReq("dbId"));
		$keyObj		= $dbObj->getString($reqObj->getReq("key"));
		//need to bind the socket to the update and delete call backs
		//make an intermediate object for the client socket that will allow subscriptions and unsubs
		$reqObj->getClient()->trackKey($keyObj);
		$reqObj->setResp("OK")->send();
	}
	protected function setCachingDisabled($reqObj)
	{
		if ($reqObj->getReq("dbId") === null) {
			throw new \Exception("Database Id attribute is mandatory");
		} elseif ($reqObj->getReq("key") === null) {
			throw new \Exception("Database key attribute is mandatory");
		}
		$reqObj->getClient()->getAuth(true, "CLIENT CACHING", $reqObj->getReq("dbId"), $reqObj->getReq("key"));
		$dbObj		= $reqObj->getClient()->getRedis()->getDatabase($reqObj->getReq("dbId"));
		$keyObj		= $dbObj->getString($reqObj->getReq("key"));
		$reqObj->getClient()->unTrackKey($keyObj);
		$reqObj->setResp("OK")->send();
	}
}