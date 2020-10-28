<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream\Xinfo;

class V1 extends Base
{
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array("STREAM", $this->getStream()->getKey()));
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->selectDb()->parse($this->getSocket()->write($this->getRawCmd())->read(true));
			$this->_isExec	= true;
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		if (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
			return $this;
		}
		
		$dArr				= $this->getClient()->parseResponse($rData);
		$rObj				= new \stdClass();
		$rObj->{$dArr[0]}	= $dArr[1];
		$rObj->{$dArr[2]}	= $dArr[3];
		$rObj->{$dArr[4]}	= $dArr[5];
		$rObj->{$dArr[6]}	= $dArr[7];
		$rObj->{$dArr[8]}	= $dArr[9];
		if ($dArr[11] === false) {
			$rObj->{$dArr[10]}	= null;
		} else {
			
			$mObj				= $this->getStream()->getMsgObj();
			$rObj->{$dArr[10]}	= $mObj;
			
			$mObj->id			= $dArr[11][0];
			$mObj->payload		= array();
			$fCount				= count($dArr[11][1]) / 2;
			for ($x=0; $x<$fCount; $x++) {
				$i						= $x*2;
				$field					= $dArr[11][1][$i];
				$value					= $dArr[11][1][($i+1)];
				$mObj->payload[$field]	= $this->getClient()->dataDecode($value);
			}
		}
		if ($dArr[13] === false) {
			$rObj->{$dArr[12]}	= null;
		} else {
			$mObj				= $this->getStream()->getMsgObj();
			$rObj->{$dArr[12]}	= $mObj;
			
			$mObj->id			= $dArr[13][0];
			$mObj->payload		= array();
			$fCount				= count($dArr[13][1]) / 2;
			for ($x=0; $x<$fCount; $x++) {
				$i						= $x*2;
				$field					= $dArr[13][1][$i];
				$value					= $dArr[13][1][($i+1)];
				$mObj->payload[$field]	= $this->getClient()->dataDecode($value);
			}
		}
		$this->setResponse($rObj);
		return $this;
	}
}