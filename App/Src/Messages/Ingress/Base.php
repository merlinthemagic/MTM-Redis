<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Messages\Ingress;

abstract class Base extends \MTM\Redis\Messages\Base
{
	public function setMsgId($id)
	{
		$this->_msgId	= $id;
		return $this;
	}
}