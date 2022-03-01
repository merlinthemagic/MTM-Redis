<?php
//© 2022 Martin Peter Madsen
namespace MTM\RedisApi\Models\Clients\V1;

abstract class Scripts extends Lists
{
	protected $_scripts=array();
	
	public function isScriptLoaded($hash)
	{
		if (is_string($hash) === false || strlen($hash) !== 40) {
			throw new \Exception("Hash must be sha1");
		} elseif (array_key_exists($hash, $this->_scripts) === false) {
			$cmdStr		= $this->getRawCmd("SCRIPT", array("EXISTS", $hash));
			$rData		= $this->newScript($cmdStr)->exec(true);
			if (is_array($rData) === false || count($rData) !== 1) {
				throw new \Exception("Invalid script exists command return");
			} elseif (reset($rData) === 1) {
				$this->_scripts[$hash]		= true;
			} else {
				$this->_scripts[$hash]		= false;
			}
		}
		return $this->_scripts[$hash];
	}
	public function loadScript($str, $throw=false)
	{
		if (is_string($str) === false) {
			throw new \Exception("Script must be string");
		}
		
		$hash		= hash("sha1", $str);
		if ($this->isScriptLoaded($hash) === false) {
			$cmdStr		= $this->getRawCmd("SCRIPT", array("LOAD", $str));
			$rData		= $this->newScript($cmdStr)->exec(true);
			if ($rData === $hash) {
				$this->_scripts[$hash]		= true;
			} else {
				throw new \Exception("Script load return does not match our hash");
			}
		} elseif ($throw === true) {
			throw new \Exception("Script already loaded");
		}
		return $this;
	}
}