<?php
//� 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Streams\V1;

class Zstance extends Groups
{
	public function terminate($throw=true)
	{
		$errObj	= null;
		if ($errObj === null) {
			return $this;
		} elseif ($throw === true) {
			throw $errObj;
		} else {
			return $errObj;
		}
		return $this;
	}
}