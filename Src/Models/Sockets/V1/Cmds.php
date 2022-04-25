<?php
//© 2020 Martin Peter Madsen
namespace MTM\RedisApi\Models\Sockets\V1;

abstract class Cmds extends Base
{
	protected $_dbId=0;
	
	public function selectDb($id)
	{
		if ($id !== $this->_dbId) {
			$this->select($id)->exec(true);
			$this->_dbId	= $id;
		}
		return $this;
	}
	public function select($id=0)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Select\V1($this);
		$cmdObj->setId($id);
		return $cmdObj;
	}
	public function ping()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Ping\V1($this);
		return $cmdObj;
	}
	public function auth($str=null)
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Auth\V1($this);
		$cmdObj->setAuth($str);
		return $cmdObj;
	}
	public function clientId()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Client\Id\V1($this);
		return $cmdObj;
	}
	public function clientCaching($bool=false)
	{
		//make sure you have selected the right database before calling me
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Client\Caching\V1($this);
		$cmdObj->setCache($bool);
		return $cmdObj;
	}
	public function clientTracking($enable=true)
	{
		//many more options
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Client\Tracking\V1($this);
		$cmdObj->setTrack($enable);
		return $cmdObj;
	}
	public function quit()
	{
		$cmdObj		= new \MTM\RedisApi\Models\Cmds\Socket\Quit\V1($this);
		return $cmdObj;
	}
}