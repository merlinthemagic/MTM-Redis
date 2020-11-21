<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Models\Clients\V1;

abstract class Authorization extends Base
{
	protected $_authSecret="";
	protected $_authImplicit=true; //by default all commands are allowed for all
	protected $_authHashes=array();
	
	public function setAuthSecretOOB($str)
	{
		//this allows us to set a secret out of band not using the setAuthSecret()
		//since that function transmits the secret on the same channel that an attacker would be monitoring 
		if (is_string($str) === false) {
			//cannot use null, will cause hashing mismatch between JS and PHP
			throw new \Exception("Input is invalid");
		}
		$this->_authSecret		= trim($str);
		return $this;
	}
	public function getAuthSecret()
	{
		return $this->_authSecret;
	}
	public function getAuth($throw=true, $cmd=null, $dbId=null, $key=null)
	{
		if (count($this->_authHashes) > 0) {
			$isset	= true;
			$hash	= $this->getAuthHash($cmd, $dbId, $key); //is the key cmd allowed in a specific DB?
			if (isset($this->_authHashes[$hash]) === false) {
				$hash	= $this->getAuthHash($cmd, null, $key); //is the key cmd allowed in all DBs?
				if (isset($this->_authHashes[$hash]) === false) {
					$hash	= $this->getAuthHash($cmd, $dbId); //is the cmd allowed in a specific DB?
					if (isset($this->_authHashes[$hash]) === false) {
						$hash	= $this->getAuthHash($cmd); //is the cmd allowed globally?
						if (isset($this->_authHashes[$hash]) === false) {
							$isset	= false;
						}
					}
				}
			}
		} else {
			$isset	= false;
		}

		$valid	= false;
		if ($isset === true) {
			if ($this->getAuthImplicit() === true) {
				$valid	= false;//listed when authentication is implicit: deny
			} else {
				$valid	= true;//not listed when authentication is implicit: deny
			}
		} elseif ($this->getAuthImplicit() === true) {
			//setting auth implicit === true
			//means comands are allowed unless specified
			$valid	= true;
		} else {
			//setting auth implicit === false
			//means comands are denied unless specified
			$valid	= false;
		}
		if ($valid === true) {
			return true;
		} elseif ($throw === true) {
			throw new \Exception("Authorization failed for: ".$cmd."/".$dbId."/".$key);
		} else {
			return $valid;
		}
	}
	public function setAuthImplicit($bool)
	{
		if (is_bool($bool) === false) {
			throw new \Exception("Input is invalid");
		}
		$this->_authImplicit	= $bool;
		return $this;
	}
	public function getAuthImplicit()
	{
		return $this->_authImplicit;
	}
	public function addAuth($cmd=null, $dbId=null, $key=null)
	{
		//if only cmd is set: adds a global directive, if a database
		//command, it will apply to all databases
		
		//if cmd + dbId set: adds a database command directive,
		//it will apply to one database
		
		//if cmd + dbId + key set: adds a database command directive,
		//it will apply to one database and one key
		
		//if cmd + key set:adds a global directive for a key, if a database
		//command, it will apply to that key in all databases
		$hash						= $this->getAuthHash($cmd, $dbId, $key);
		$this->_authHashes[$hash]	= true; //use true so we can use isset, its faster than array_key_exists
		return $this;
	}
	protected function getAuthHash($cmd=null, $dbId=null, $key=null)
	{
		$str	= "";
		if ($cmd !== null) {
			if (is_string($cmd) === false) {
				throw new \Exception("Cmd input is invalid");
			}
			$str	.= strtoupper(trim($cmd));
		}
		if ($dbId !== null) {
			if (is_int($dbId) === false) {
				throw new \Exception("DbId input is invalid");
			}
			$str	.= $dbId;
		}
		if ($key !== null) {
			if (is_string($key) === false) {
				throw new \Exception("Key input is invalid");
			}
			$str	.= trim($key);//keys are case sensitive
		}
		return hash("sha256", $str);
	}
}