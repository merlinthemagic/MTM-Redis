<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Channels;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	protected $_clientObj=null;
	protected $_name=null;
	protected $_msgs=array();
	protected $_isSub=false;
	protected $_cbs=null;
	
	public function __construct($clientObj, $name)
	{
		$this->_clientObj	= $clientObj;
		$this->_name		= $name;
		parent::__construct();
	}
	public function __destruct()
	{
		$this->unsubscribe();
	}
	public function getClient()
	{
		return $this->_clientObj;
	}
	public function getName()
	{
		return $this->_name;
	}
	public function getMessage($timeout=1000)
	{
		$rMsgs	= $this->getMessages(1, $timeout);
		if (count($rMsgs) > 0) {
			return reset($rMsgs);
		} else {
			return null;
		}
	}
	public function getMessages($count=-1, $timeout=1)
	{
		if ($timeout > 0) {
			$this->getClient()->subSocketRead(false, $timeout); //fetch new messages
		}
		$max	= count($this->_msgs); //max count
		if ($count < 0 || $count > $max) {
			$count	= $max; //get all
		}
		$rMsgs	= array();
		$i		= 0;
		foreach($this->_msgs as $mId => $msgObj) {
			$i++;
			$rMsgs[]	= $msgObj;
			unset($this->_msgs[$mId]);
			if ($count == $i) {
				break;
			}
		}
		return $rMsgs;
	}
	public function addCb($obj, $method)
	{
		if ($this->_cbs === null) {
			$this->_cbs	= array();
		}
		$this->_cbs[]	= array($obj, $method);
		return $this;
	}
	public function removeCb($obj, $method)
	{
		if ($this->_cbs !== null) {
			foreach ($this->_cbs as $index => $cb) {
				if (
					$cb[1] === $method
					&& $cb[0] === $obj
				) {
					unset($this->_cbs[$index]);
					if (count($this->_cbs) === 0) {
						$this->_cbs	= null;
					}
					break;
				}
			}
		}
		return $this;
	}
	protected function exeCb($msgObj)
	{
		if ($this->_cbs !== null) {
			$params	= array($this, $msgObj);
			foreach ($this->_cbs as $cb) {
				try {
					call_user_func_array($cb, $params);
				} catch (\Exception $e) {
					//if you throw we drop you, control yourself!
					$this->removeCb($cb[0], $cb[1]);
				}
			}
			return true;
			
		} else {
			return false;
		}
	}
	protected function getMsgObj()
	{
		$msgObj				= new \stdClass();
		$msgObj->payload	= null;
		$msgObj->loadTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
		return $msgObj;
	}
}