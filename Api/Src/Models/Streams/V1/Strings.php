<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Streams\V1;

abstract class Strings extends Groups
{
	public function xAdd($fieldValues=array(), $id="*")
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Stream\Xadd\V1($this);
		foreach ($fieldValues as $field => $value) {
			$cmdObj->addField($field, $value);
		}
		$cmdObj->setId($id);
		return $cmdObj;
	}
	public function xDel($id=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Stream\Xdel\V1($this);
		$cmdObj->setId($id);
		return $cmdObj;
	}
	public function xInfo()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Stream\Xinfo\V1($this);
		return $cmdObj;
	}
	public function xLen()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Stream\Xlen\V1($this);
		return $cmdObj;
	}
	public function xRange()
	{
		//too many options to take parameters
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Stream\Xrange\V1($this);
		return $cmdObj;
	}
}