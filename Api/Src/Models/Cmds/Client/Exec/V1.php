<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Cmds\Client\Exec;

class V1 extends Base
{
	public function getRawCmd()
	{
		return $this->getClient()->getRawCmd($this->getBaseCmd(), array());
	}
	public function exec($throw=false)
	{
		if ($this->isExec() === false) {
			$this->parse($this->getSocket()->write($this->getRawCmd())->read(true));
			$this->getSocket()->setMulti(false);
		}
		return $this->getResponse($throw);
	}
	public function parse($rData)
	{
		$rArr	= $this->getClient()->parseResponse($rData);
		
		echo "\n <code><pre> \nClass:  ".get_class($this)." \nMethod:  ".__FUNCTION__. "  \n";
		//var_dump($_SERVER);
		echo "\n 2222 \n";
		print_r($rArr);
		echo "\n FIX this so it uses the response parser \n";
		print_r($rData);
		echo "\n ".time()."</pre></code> \n ";
		die("end");
		if ($rVal instanceof \Exception) {
			$this->setException($rVal);
		} elseif (is_int($rVal) === true && $rVal === 0) {
			$this->setException(new \Exception("Id did not exist: ".$this->getId()));
		} else {
			$this->setResponse($rVal);
		}
		return $this;
		$nPos			= strpos($rData, "\r\n");
		$qLen			= intval(substr($rData, 1, $nPos));
		if ($qLen < 0) {
			//maybe a watched key was changed
			$this->setResponse(false)->setException(new \Exception("Transaction failed"));
		} else {
			$rObj			= new \stdClass();
			$rObj->count	= $qLen;
			$rObj->returns	= array();
			$rData	= substr($rData, ($nPos+2));
			$i=0;
			while(true) {
				$i++;
				
				$nPos	= strpos($rData, "\r\n");
				if (strpos($rData, "\$") === 0) {
					$dLen	= intval(substr($rData, 1, $nPos));
					if ($dLen < 0) {
						$ePos	= 5;
					} else {
						$ePos	= ($nPos+$dLen+4);
					}
				} else {
					$ePos	= ($nPos+2);
				}
				
				$data				= substr($rData, 0, $ePos);
				$rData				= substr($rData, $ePos);
				$rObj->returns[]	= $data;
				
				if ($rData == "") {
					break;
				} elseif ($i > $qLen) {
					$this->setException(new \Exception("Queue length: ".$qLen." does not match return data count: ".$i));
					break;
				}
			}
			$this->setResponse($rObj);
		}
		return $this;
	}
	protected function discard()
	{
		$this->parse($this->getClient()->mainSocketWrite($this->getClient()->getRawCmd("DISCARD", array()))->mainSocketRead(true));
		return $this;
	}
}