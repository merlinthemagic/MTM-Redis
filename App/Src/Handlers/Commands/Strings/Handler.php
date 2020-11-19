<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands\Strings;

class Handler extends Get
{
	public function handle($reqObj)
	{
		if ($reqObj->getL3() == "Get") {
			return $this->get($reqObj);
		} elseif ($reqObj->getL3() == "Caching") {
			if ($reqObj->getL4() == "Enable") {
				return $this->setCachingEnabled($reqObj);
			} elseif ($reqObj->getL4() == "Disable") {
				return $this->setCachingDisabled($reqObj);
			}
		}
		$this->routeInvalid($reqObj);
	}
	
}