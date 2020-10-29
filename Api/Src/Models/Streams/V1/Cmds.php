<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Streams\V1;

abstract class Cmds extends Base
{
	protected $_lastId=null;
	
	public function getNext()
	{
		//reads messages from a stream from the first to the last
		//increments with each call
		$cmdObj		= $this->xRead();
		if ($this->_lastId === null) {
			$msgObjs	= $cmdObj->getFirst()->exec(false);
		} else {
			$msgObjs	= $cmdObj->getNextById($this->_lastId)->exec(false);
		}
		if (count($msgObjs) === 0) {
			return null;
		} else {
			$msgObj			= reset($msgObjs);
			$this->_lastId	= $msgObj->id;
			return $msgObj;
		}
	}
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
		//too many options to take parameters here
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Stream\Xrange\V1($this);
		return $cmdObj;
	}
	public function xRead()
	{
		//too many options to take parameters here
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Stream\Xread\V1($this);
		return $cmdObj;
	}
	public function delete()
	{
		//delete self
		$this->getDb()->deleteStream($this);
		return $this;
	}
}