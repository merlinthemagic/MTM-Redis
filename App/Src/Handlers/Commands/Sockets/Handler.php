<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands\Sockets;

class Handler extends Get
{
	public function handle($reqObj)
	{
		if ($reqObj->getL3() == "Client") {
			if ($reqObj->getL4() == "Id") {
				return $this->getClientId($reqObj);
			}
		}
		$this->routeInvalid($reqObj);
	}
	
}