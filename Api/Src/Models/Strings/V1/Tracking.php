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