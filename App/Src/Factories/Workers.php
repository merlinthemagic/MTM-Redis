<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Factories;

class Workers extends Base
{
	public function getV1()
	{
		$workObj	= new \MTM\Redis\Workers\V1();
		return $workObj;
	}
}