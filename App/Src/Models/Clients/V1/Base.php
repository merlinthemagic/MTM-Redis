<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Models\Clients\V1;

abstract class Base extends \MTM\Redis\Models\Clients\Base
{
	protected $_clientId=null;
	
	public function getClientId()
	{
		return $this->_clientId;
	}
}