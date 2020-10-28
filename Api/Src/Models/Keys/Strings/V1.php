<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Keys\Strings;

class V1 extends Base
{
	protected $_isTracking=false;
	protected $_exists=null;
	protected $_data=null;
	protected $_updateCb=null;
	protected $_delCb=null;
	
	public function getData()
	{
		if ($this->isTracking() === false) {
			$this->pullData();
		}
		return $this->_data;
	}
	public function getExists()
	{
		if ($this->isTracking() === false) {
			$this->pullData();
		}
		return $this->_exists;
	}
	public function setData($data, $timeout=2000)
	{
		$watchObj		= $this->getDb()->watch($this->getKey());
		$cmdObj			= $this->getDb()->set($this->getKey(), $data);
		$wmObj			= $this->getClient()->newWatchMulti(array($watchObj), array($cmdObj));
		$wmObj->exec(true, $timeout);
		//fill data and if tracking resubscribe to cache. if no loop is on we dont get a message the key was updated
		//but if using "optin" we still get unsubscribed
		$this->pullData();
		return $this;
	}
	public function delete($timeout=2000)
	{
		$watchObj		= $this->getDb()->watch($this->getKey());
		$cmdObj			= $this->getDb()->delete($this->getKey());
		$wmObj			= $this->getClient()->newWatchMulti(array($watchObj), array($cmdObj));
		$wmObj->exec(true, $timeout);
		$this->pullData();
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
	public function enableTracking()
	{
		if ($this->isTracking() === false) {
			if ($this->getSocket()->isTracked() === false) {
				$this->getSocket()->enableTracking();
			}
			$this->getDb()->trackKey($this);
			$this->_isTracking	= true;
			$this->pullData();
			if ($this->getExists() === false) {
				$this->disableTracking();
				//update and delete does not take effect if the key does not exist
				throw new \Exception("Key does not exist, tracking is not possible");
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
			$this->pullData();
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
	protected function pullData()
	{
		if ($this->isTracking() === true) {
			if ($this->getSocket()->getTrackMode() === "OPTIN") {
				//we have to explicitly opt into tracking
				$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Client\Caching\V1($this->getSocket());
				$cmdObj->setCache(true)->exec(true);
			}
		} elseif ($this->isTracking() === false) {
			if (
				$this->getSocket()->isTracked() === true
				&& $this->getSocket()->getTrackMode() === "OPTOUT"
			) {
				//we have to explicitly opt out of tracking
				$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Client\Caching\V1($this->getSocket());
				$cmdObj->setCache(false)->exec(true);
			}
		}
		$cmdObj	= $this->getDb()->get($this->getKey());
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
	public function terminate($throw=false)
	{
		$this->disableTracking();
	}
}