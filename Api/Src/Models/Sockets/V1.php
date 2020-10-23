<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets;

class V1 extends Base
{
	protected $_id=null;
	protected $_sockObj=null;
	protected $_isInit=false;
	protected $_isTerm=false;
	
	public function getId()
	{
		return $this->_id;
	}
	public function getSocket()
	{
		return $this->_sockObj;
	}
	public function write($strCmd)
	{
		$cmdParts	= str_split($strCmd, $this->getParent()->getChunkSize());
		foreach ($cmdParts as $cmdPart) {
			$written	= fwrite($this->getSocket(), $cmdPart);
			if (strlen($cmdPart) != $written) {
				throw new \Exception("Failed to write command");
			}
		}
		return $this;
	}
	public function read($throw=false, $timeout=5000)
	{
		$tTime		= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + ($timeout / 1000);
		$rData		= "";
		while(true) {
			$data 	= fgets($this->getSocket());
			if ($data != "") {
				$rData	.= $data;
			} elseif ($rData != "") {
				return $rData;
			} elseif ($tTime < \MTM\Utilities\Factories::getTime()->getMicroEpoch()) {
				if ($throw === true) {
					throw new \Exception("Read command timeout");
				} else {
					return null;
				}
			}
		}
	}
	public function initialize()
	{
		if ($this->_isInit === false) {

			if ($this->getParent()->getSslCert() === null) {
				$strConn	= $this->getParent()->getProtocol()."://".$this->getParent()->getHostname().":".$this->getParent()->getPort()."/";
			} else {
				//steal logic from wsSocket client
				throw new \Exception("Not yet handled for tls");
			}
			
			$sockRes 		= stream_socket_client($strConn, $errno, $errstr, $this->getParent()->getTimeout(), STREAM_CLIENT_CONNECT);
			if (is_resource($sockRes) === false) {
				throw new \Exception("Socket Error: " . $errstr, $errno);
			}
				
			stream_set_blocking($sockRes, false);
			stream_set_chunk_size($sockRes, $this->getParent()->getChunkSize());
			
			$this->_sockObj	= $sockRes;
			
			if ($this->getParent()->getAuth() != "") {
				
				try {
				
					$cmdObj		= new \MTM\RedisApi\Models\Cmds\Auth($this);
					$cmdObj->setAuth($this->getParent()->getAuth())->exec(true);

				} catch (\Exception $e) {
					fclose($this->_sockObj);
					$this->_sockObj	= null;
					throw $e;
				}
			}
			
			try {
				
				$cmdObj		= new \MTM\RedisApi\Models\Cmds\ClientId($this);
				$this->_id	= $cmdObj->exec(true);
				
			} catch (\Exception $e) {
				fclose($this->_sockObj);
				$this->_sockObj	= null;
				throw $e;
			}

			$this->_isInit	= true;
		}
		return $this;
	}
	public function terminate($throw=false)
	{
		if ($this->_isInit === true && $this->_isTerm === false) {
			$this->read(false, -1); //clear the socket before quitting
			$cmdObj		= new \MTM\RedisApi\Models\Cmds\Quit($this);
			$cmdObj->exec($throw);
			
			fclose($this->_sockObj);
			$this->_isTerm	= true;
		}
		return $this;
	}
}