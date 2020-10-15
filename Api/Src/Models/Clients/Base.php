<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients;

abstract class Base extends \MTM\RedisApi\Models\Base
{
	public function __destruct()
	{
		$this->quit();
	}
}