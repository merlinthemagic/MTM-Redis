<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

class Zstance extends Transactions
{
	public function terminate($throw=true)
	{
		$errObj	= null;
		foreach ($this->getStreams() as $streamObj) {
			try {
				$this->removeStream($streamObj);
			} catch (\Exception $e) {
				if ($errObj === null) {
					$errObj	= $e;
				}
			}
		}
		foreach ($this->getLists() as $listObj) {
			try {
				$this->removeList($listObj);
			} catch (\Exception $e) {
				if ($errObj === null) {
					$errObj	= $e;
				}
			}
		}
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