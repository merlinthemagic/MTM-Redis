<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream\Xread;

class V1 extends Base
{
	protected $_method=null;
	protected $_startId=null;
	protected $_endId=null;
	protected $_count=null;
	protected $_block=null; //0 block indef, -1 dont block
	
	public function getFirst()
	{
		$this->_count	= 1;
		$this->_block	= -1;
		$this->_startId	= "0-0";
		$this->_method	= __FUNCTION__;
		return $this;
	}
	public function getNextById($id)
	{
		$this->_count	= 1;
		$this->_block	= -1;
		$this->_startId	= $id;
		$this->_method	= __FUNCTION__;
		return $this;
	}
	public function getRawCmd()
	{
		$args		= array();
		$args[]		= "COUNT";
		$args[]		= $this->_count;
		if ($this->_block > -1) {
			$args[]		= "BLOCK";
			$args[]		= $this->_block;
		}
		$args[]		= "STREAMS";
		$args[]		= $this->getStream()->getKey();
		$args[]		= $this->_startId;
		
		return $this->getClient()->getRawCmd($this->getBaseCmd(), $args);
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
		
		$rObjs		= array();
		$dArr		= $this->getClient()->parseResponse($rData);
		foreach ($dArr as $stream) {
			foreach ($stream[1] as $msg) {
				
				$mObj				= $this->getStream()->getMsgObj();
				$mObj->id			= $msg[0];
				$mObj->payload		= array();
				$fCount				= count($msg[1]) / 2;
				for ($x=0; $x<$fCount; $x++) {
					$i						= $x*2;
					$field					= $msg[1][$i];
					$value					= $msg[1][($i+1)];
					$mObj->payload[$field]	= $this->getClient()->dataDecode($value);
				}
				$rObjs[]		= $mObj;
			}
		}
		$this->setResponse($rObjs);
		return $this;
	}
}