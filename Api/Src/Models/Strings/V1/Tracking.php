<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Strings\V1;

abstract class Tracking extends Cmds
{
	protected $_isTracking=false;
	protected $_exists=null;
	protected $_data=null;
	protected $_updateCb=null;
	protected $_delCb=null;
	
	public function getData()
	{
		if ($this->isTracking() === false) {
			$this->reTrack();
		}
		return $this->_data;
	}
	public function getExists()
	{
		if ($this->isTracking() === false) {
			$this->reTrack();
		}
		return $this->_exists;
	}
	public function setData($data, $timeout=2000)
	{
		$watchObj		= $this->getDb()->watch($this->getKey());
		$cmdObj			= $this->set($data);
		$wmObj			= $this->getClient()->newWatchMulti(array($watchObj), array($cmdObj));
		$wmObj->exec(true, $timeout);
		//fill data and if tracking resubscribe to cache. if no loop is on we dont get a message the key was updated
		//but if using "optin" we still get unsubscribed
		$this->reTrack();
		return $this;
	}
	public function setUpdateCb($obj, $method)
	{
		$this->_updateCb	= array($obj, $method);
		return $this;
	}
	public function setDeleteCb($obj, $method)
	{
		$this->_delCb	= array($obj, $method);
		return $this;
	}
	public function isTracking()
	{
		return $this->_isTracking;
	}
	public function enableTracking($create=false, $value=null)
	{
		if ($this->isTracking() === false) {
			if ($this->getSocket()->isTracked() === false) {
				$this->getSocket()->enableTracking();
			}
			$this->getDb()->trackKey($this);
			$this->_isTracking	= true;
			$this->reTrack();
			if ($this->getExists() === false) {
				if ($create === false) {
					$this->disableTracking();
					//update and delete does not take effect if the key does not exist
					throw new \Exception("Key does not exist, tracking is not possible");
				} else {
					//create the key.. if it was not created in the meantime
					$this->setNx($value)->exec(false);
					$this->reTrack();
				}
			}
		}
		return $this;
	}
	public function disableTracking()
	{
		if ($this->isTracking() === true) {
			//TODO: How does one unsubscribe? watch + set?
			$this->getDb()->untrackKey($this);
			$this->_isTracking	= false;
		}
		return $this;
	}
	public function trackInvalidated()
	{
		if ($this->isTracking() === true) {
			$curExists	= $this->_exists;
			$this->reTrack();
			if ($curExists === true && $this->_exists === false) {
				if ($this->_delCb !== null) {
					try {
						call_user_func_array($this->_delCb, array($this));
					} catch (\Exception $e) {
						//Control yourself!
					}
				}
			} else {
				if ($this->_updateCb !== null) {
					try {
						call_user_func_array($this->_updateCb, array($this));
					} catch (\Exception $e) {
						//Control yourself!
					}
				}
			}
		}
		return $this;
	}
	protected function reTrack()
	{
		$cmdObj	= $this->get();
		$data	= $cmdObj->exec(false);
		if ($cmdObj->getException() === null) {
			$this->_data	= $data;
			$this->_exists	= true;
		} elseif ($cmdObj->getException()->getCode() == 7554) {
			$this->_data	= null;
			$this->_exists	= false;
		} else {
			throw $cmdObj->getException();
		}
		return $this->_data;
	}
}