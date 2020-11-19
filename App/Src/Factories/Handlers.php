<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Factories;

class Handlers extends Base
{
	public function handle($reqObj)
	{
		try {
			if ($reqObj->getL1() == "Commands") {
				return $this->getCommands()->handle($reqObj);
			}
			$this->routeInvalid($reqObj);
		
		} catch (\Exception $e) {
			$reqObj->setException($e)->send();
		}
	}
	public function getCommands()
	{
		if (array_key_exists(__FUNCTION__, $this->_s) === false) {
			$this->_s[__FUNCTION__]	= new \MTM\Redis\Handlers\Commands\Handler();
		}
		return $this->_s[__FUNCTION__];
	}
	public function routeInvalid($reqObj)
	{
		throw new \Exception("Invalid Route: ".$reqObj->getL1()."/".$reqObj->getL2()."/".$reqObj->getL3()."/".$reqObj->getL4());
	}
}