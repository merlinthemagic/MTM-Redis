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
		//there is room for lots of optimization here
		if (strpos($rData, "-ERR") === 0) {
			$this->setResponse(false)->setException(new \Exception("Error: ".$rData));
			return $this;
		}
		$nPos			= strpos($rData, "\r\n");
		$mLen			= intval(substr($rData, 1, $nPos));
		if ($mLen > 0) {
			$rObj		= new \stdClass();
			$rData		= substr($rData, ($nPos+2));
			while(true) {
				$nPos	= strpos($rData, "\r\n");
				$aLen	= intval(substr($rData, 1, $nPos));
				$attr	= substr($rData, ($nPos+2), $aLen);
				$rData	= substr($rData, ($nPos+$aLen+4));
				
				if (in_array($attr, array("length", "radix-tree-keys", "radix-tree-nodes", "groups")) === true) {
					//int inbound
					$nPos			= strpos($rData, "\r\n");
					$aVal			= intval(substr($rData, 1, $nPos));
					$rData			= substr($rData, ($nPos+2));
					$rObj->$attr	= $aVal;
				} elseif (in_array($attr, array("last-generated-id")) === true) {
					//string inbound
					$nPos			= strpos($rData, "\r\n");
					$vLen			= intval(substr($rData, 1, $nPos));
					$aVal			= substr($rData, ($nPos+2), $vLen);
					$rData			= substr($rData, ($nPos+$vLen+4));
					$rObj->$attr	= $aVal;
				} elseif (in_array($attr, array("first-entry", "last-entry")) === true) {
					//array or null inbound
					$nPos			= strpos($rData, "\r\n");
					$arrLen			= intval(substr($rData, 1, $nPos));
					$rData			= substr($rData, ($nPos+2));
					if ($arrLen < 0) {
						$rObj->$attr	= null;
					} else {
						
						//get id
						$nPos		= strpos($rData, "\r\n");
						$idLen		= intval(substr($rData, 1, $nPos));
						$idVal		= substr($rData, ($nPos+2), $idLen);
						$rData		= substr($rData, ($nPos+4+$idLen));
						
						//find the fields
						$nPos		= strpos($rData, "\r\n");
						$iCount		= intval(substr($rData, 1, $nPos)) / 2;
						$rData		= substr($rData, ($nPos+2));
						
						$fields		= array();
						for ($x=0; $x<$iCount; $x++) {
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
						
						$rObj->$attr			= new \stdClass();
						$rObj->$attr->id		= $idVal;
						$rObj->$attr->fields	= $fields;
					}
					
				} else {
					throw new \Exception("Not handled for return: ".$rData);
				}
				
				if ($rData == "") {
					break;
				}
			}
			$this->setResponse($rObj);
		} else {
			throw new \Exception("Not handled for return: ".$rData);
		}
		return $this;
	}
}