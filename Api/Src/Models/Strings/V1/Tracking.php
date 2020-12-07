<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Strings\V1;

abstract class Tracking extends Cmds
{
	protected $_isTracking=false;
	protected $_exists=null;
	protected $_data=null;
	protected $_updateCbs=array();
	protected $_delCbs=array();
	
	protected $_acquireCmd=null;
	protected $_releaseCmd=null;
	protected $_lockExpire=0;
	
	public function getData()
	{
		if ($this->isTracking() === false) {
			$this->refreshCache();
		}
		return $this->_data;
	}
	public function getExists()
	{
		if ($this->isTracking() === false) {
			$this->refreshCache();
		}
		return $this->_exists;
	}
	public function isLocked()
	{
		if ($this->_lockExpire > \MTM\Utilities\Factories::getTime()->getMicroEpoch()) {
			return true;
		} else {
			return false;
		}
	}
	public function acquireLock($timeout=30000, $expire=5000, $poll=true)
	{
		if ($this->isLocked() === true) {
			throw new \Exception("Cannot lock, you already have a lock");
		}
		$lockVal	= \MTM\Utilities\Factories::getStrings()->getRandomByRegex(20);
		if ($this->_acquireCmd === null) {
			$lockObj			= $this->getDb()->getString(hash("sha256", $this->getKey()."--lock"));
			$this->_acquireCmd	= $lockObj->setNxPx($lockVal, $expire);
		} else {
			$this->_acquireCmd->setValue($lockVal)->setExpire($expire);
		}

		$tTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch() + ($timeout / 1000);
		while (true) {
			$cTime	= \MTM\Utilities\Factories::getTime()->getMicroEpoch();
			$this->_acquireCmd->reset()->exec(false);
			if ($this->_acquireCmd->getException() === null) {
				$this->_lockExpire	= $cTime + ($expire / 1000);
				if ($poll === true) {
					//lots can have happened since we asked for a lock
					$this->getClient()->pollSub();
				}
				return $this;
			} elseif ($this->_acquireCmd->getException()->getCode() !== 8865) {
				throw $this->_acquireCmd->getException(); //other error than lock exists
			} elseif ($tTime < $cTime) {
				throw new \Exception("Failed to obtain a lock");
			}
		}
	}
	public function releaseLock()
	{
		if ($this->isLocked() === true) {
			$lockCmd			= $this->_acquireCmd->getString();
			if ($this->_releaseCmd === null) {
				$this->_releaseCmd	= $lockCmd->delMatchValue($this->_acquireCmd->getValue());
			} else {
				$this->_releaseCmd->setValue($this->_acquireCmd->getValue());
			}
			
			$this->_releaseCmd->reset()->exec(false);
			if ($this->_releaseCmd->getException() !== null) {
				//we dont really care why it failed, do we?
			}
			$this->_lockExpire	= 0;
		}
		return $this;
	}
	public function setData($data)
	{
		$this->set($data)->exec(true);
		$cData	= $this->refreshCache();
		if ($cData !== $data) {
			//data was replaced right after we set it
			$this->trackInvalidated();
		}
		return $this;
	}
	public function setDataNx($data)
	{
		$this->setNx($data)->exec(false);
		$this->refreshCache();
		return $this;
	}
	public function setUpdateCb($obj, $method)
	{
		$this->_updateCbs[]	= array($obj, $method);
		return $this;
	}
	public function removeUpdateCb($obj, $method)
	{
		foreach ($this->_updateCbs as $index => $cb) {
			if ($cb[0] === $obj && $cb[1] === $method) {
				unset($this->_updateCbs[$index]);
				break;
			}
		}
		return $this;
	}
	public function setDeleteCb($obj, $method)
	{
		$this->_delCbs[]	= array($obj, $method);
		return $this;
	}
	public function removeDeleteCb($obj, $method)
	{
		foreach ($this->_delCbs as $index => $cb) {
			if ($cb[0] === $obj && $cb[1] === $method) {
				unset($this->_delCbs[$index]);
				break;
			}
		}
		return $this;
	}
	public function isTracking()
	{
		return $this->_isTracking;
	}
	public function enableTracking()
	{
		if ($this->isTracking() === false) {
			$this->getSocket()->enableTracking();
			$this->getDb()->trackKey($this);
			$this->_isTracking	= true;
			$this->refreshCache();
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
			$this->refreshCache();
			if ($curExists === true && $this->_exists === false) {
				foreach ($this->_delCbs as $cb) {
					try {
						call_user_func_array($cb, array($this));
					} catch (\Exception $e) {
						//Control yourself!
					}
				}
			} else {
				foreach ($this->_updateCbs as $cb) {
					try {
						call_user_func_array($cb, array($this));
					} catch (\Exception $e) {
						//Control yourself!
					}
				}
			}
		}
		return $this;
	}
	protected function refreshCache()
	{
		$cmdObj		= $this->get(); //recaches on its own
		$data		= $cmdObj->exec(false);
		if ($cmdObj->getException() === null) {
			
			if (
				$data instanceof \stdClass
				&& $this->_data instanceof \stdClass
			) {
				//maintain the referances if user is using objects
				//makes get methods much easier.
				//also makes setting data 30% faster for some reason
				foreach ($this->_data as $prop => $value) {
					if (property_exists($data, $prop) === false) {
						unset($this->_data->$prop);
					}
				}
				foreach ($data as $prop => $value) {
					$this->_data->$prop	= $value;
				}

			} else {
				$this->_data	= $data;
				$this->_exists	= true;
			}
			
		} elseif ($cmdObj->getException()->getCode() == 7554) {
			$this->_data	= null;
			$this->_exists	= false;
		} else {
			throw $cmdObj->getException();
		}
		return $this->_data;
	}
}