<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands;

class Handler extends \MTM\Redis\Handlers\Base
{
	public function handle($reqObj)
	{
		if ($reqObj->getL2() == "Strings") {
			return $this->getStrings()->handle($reqObj);
		} elseif ($reqObj->getL2() == "Sockets") {
			return $this->getSockets()->handle($reqObj);
		}

		$this->routeInvalid($reqObj);
	}
	protected function getSockets()
	{
		if (array_key_exists(__FUNCTION__, $this->_s) === false) {
			$this->_s[__FUNCTION__]	= new \MTM\Redis\Handlers\Commands\Sockets\Handler();
		}
		return $this->_s[__FUNCTION__];
	}
	protected function getStrings()
	{
		if (array_key_exists(__FUNCTION__, $this->_s) === false) {
			$this->_s[__FUNCTION__]	= new \MTM\Redis\Handlers\Commands\Strings\Handler();
		}
		return $this->_s[__FUNCTION__];
	}
}