<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Lists\V1;

class Zstance extends Tracking
{
	public function terminate($throw=true)
	{
		if ($this->isTerm() === false) {
			$this->setTerm();
			$errObj	= null;
			$this->disableTracking();
			$this->getDb()->removeString($this);
			if ($errObj === null) {
				return $this;
			} elseif ($throw === true) {
				throw $errObj;
			} else {
				return $errObj;
			}
		}
		return $this;
	}
}