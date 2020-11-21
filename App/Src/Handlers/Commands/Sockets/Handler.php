<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Handlers\Commands\Sockets;

class Handler extends Cmds
{
	public function handle($reqObj)
	{
		if ($reqObj->getL3() == "Client") {
			if ($reqObj->getL4() == "Id") {
				return $this->getClientId($reqObj);
			}
		} elseif ($reqObj->getL3() == "Ping") {
			return $this->ping($reqObj);
		}
		$this->routeInvalid($reqObj);
	}
	
}