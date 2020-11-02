<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Databases\V1;

class Zstance extends Transactions
{
	public function exists($key=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Exists\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function delete($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Del\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function expire($key, $secs)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Expire\V1($this);
		$cmdObj->setKey($key)->setExpire($secs);
		return $cmdObj;
	}
	public function ttl($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Ttl\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function pExpire($key, $ms)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Pexpire\V1($this);
		$cmdObj->setKey($key)->setExpire($ms);
		return $cmdObj;
	}
	public function pTtl($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Pttl\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
	public function type($key)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Db\Type\V1($this);
		$cmdObj->setKey($key);
		return $cmdObj;
	}
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