<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Lists\V1;

abstract class Tracking extends Cmds
{
	protected $_isTracking=false;
	protected $_exists=false;
	protected $_updateCb=null;
	protected $_delCb=null;

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
			$this->getSocket()->enableTracking();
			$this->getDb()->trackKey($this);
			$this->_isTracking	= true;
			$this->reTrack();
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
		$this->getDb()->trackingPreCmd($this); //re enable tracking
		$len	= $this->lLen()->exec(true);
		if ($len > 0) {
			$this->_exists	= true;
		} elseif ($len === 0) {
			$this->_exists	= false;
		} else {
			throw new \Exception("Not handled for response: ".$len);
		}
		return $this;
	}
}