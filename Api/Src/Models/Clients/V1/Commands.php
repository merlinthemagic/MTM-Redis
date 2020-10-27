<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

abstract class Commands extends Channels
{
	public function newMulti($cmdObjs=array())
	{
		$mObj		= new \MTM\RedisApi\Models\Cmds\Client\Multi\V1($this);
		foreach ($cmdObjs as $cmdObj) {
			$mObj->addCmd($cmdObj);
		}
		return $mObj;
	}
	public function newWatchMulti($watchObjs=array(), $cmdObjs=array())
	{
		$wmObj		= new \MTM\RedisApi\Models\Cmds\Client\WatchMulti\V1($this);
		foreach ($watchObjs as $watchObj) {
			$wmObj->addWatch($watchObj);
		}
		foreach ($cmdObjs as $cmdObj) {
			$wmObj->addCmd($cmdObj);
		}
		return $wmObj;
	}
	public function newUnwatch()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Client\Unwatch\V1($this);
		return $cmdObj;
	}
	public function newExec()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Client\Exec\V1($this);
		return $cmdObj;
	}
	public function newDiscard()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Client\Discard\V1($this);
		return $cmdObj;
	}
	public function newEval($exp=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Client\Evaluate\V1($this);
		$cmdObj->setExp($exp);
		return $cmdObj;
	}
	
}