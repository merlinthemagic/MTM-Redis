<?php
//© 2020 Martin Peter Madsen
namespace MTM\Redis\Factories;

class Logging extends Base
{
	public function exception($e, $throw=false)
	{
		$logPath	= "/dev/shm/merlin.txt";
		if (file_exists($logPath) === false || is_writable($logPath) === true) {
			$errLines	= array();
			$errLines[]	= date("Y/m/d H:i:s");
			$errLines[]	= $e->getMessage();
			$errLines[]	= $e->getCode();
			$errLines[]	= $e->getFile();
			$errLines[]	= $e->getLine();
			file_put_contents($logPath, "\n\n" . __METHOD__ . ": \n" . print_r($e->getTraceAsString(), true) . "\n", FILE_APPEND);
			file_put_contents($logPath, "\n\n" . __METHOD__ . ": \n" . print_r(implode("\n", $errLines), true) . "\n", FILE_APPEND);
		}
	}
}