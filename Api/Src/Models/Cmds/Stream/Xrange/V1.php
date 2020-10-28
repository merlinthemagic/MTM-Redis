<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Stream\Xrange;

class V1 extends Base
{
	protected $_method=null;
	protected $_startId=null;
	protected $_endId=null;
	protected $_count=null;
	
	public function setAll()
	{
		$this->_startId	= "-";
		$this->_endId	= "+";
		$this->_method	= __FUNCTION__;
		return $this;
	}
	public function setStartIdWithCount($id, $count)
	{
		$this->_startId	= $id;
		$this->_count	= $count;
		$this->_method	= __FUNCTION__;
		return $this;
	}
	public function getRawCmd()
	{
		$args	= array($this->getStream()->getKey());
		if ($this->_method == "setStartIdWithCount") {
			$args[]		= $this->_startId;
			$args[]		= "+";
			$args[]		= "COUNT";
			$args[]		= $this->_count;
		} elseif ($this->_method == "setAll") {
			$args[]		= $this->_startId;
			$args[]		= $this->_endId;
		} else {
			throw new \Exception("Not handled for method: ".$this->_method);
		}
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
		$rObjs			= array();
		$nPos			= strpos($rData, "\r\n");
		$eleCount		= intval(substr($rData, 1, $nPos));
		$rData			= substr($rData, ($nPos+2));
		if ($eleCount > 0) {
			for ($x=0; $x<$eleCount; $x++) {
				
				//get element subcount, always 2
				$nPos		= strpos($rData, "\r\n");
// 				$subCount	= intval(substr($rData, 1, $nPos));
				$rData		= substr($rData, ($nPos+2));
				
				$nPos		= strpos($rData, "\r\n");
				$eLen		= intval(substr($rData, 1, $nPos));
				$eleId		= substr($rData, ($nPos+2), $eLen);
				$rData		= substr($rData, ($nPos+4+$eLen));

				$nPos			= strpos($rData, "\r\n");
				$arrlen			= intval(substr($rData, 1, $nPos)) /2;
				$rData			= substr($rData, ($nPos+2));
				
				$fields		= array();
				for ($y=0; $y<$arrlen; $y++) {
					
					$nPos		= strpos($rData, "\r\n");
					$fLen		= intval(substr($rData, 1, $nPos));
					$fVal		= substr($rData, ($nPos+2), $fLen);
					$rData		= substr($rData, ($nPos+4+$fLen));
					
					$nPos		= strpos($rData, "\r\n");
					$vLen		= intval(substr($rData, 1, $nPos));
					$vVal		= substr($rData, ($nPos+2), $vLen);
					$rData		= substr($rData, ($nPos+4+$vLen));

					$fields[$fVal]	= $this->getClient()->dataDecode($vVal);
				}
				
				$rObj			= $this->getStream()->getMsgObj();
				$rObj->id		= $eleId;
				$rObj->payload	= $fields;
				$rObjs[]		= $rObj;
			}
		}
		
		if ($rData != "") {
			throw new \Exception("Not handled for return: ".$rData);
		}
		$this->setResponse($rObjs);
		return $this;
	}
}