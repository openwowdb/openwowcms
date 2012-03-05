<?php
/************************************************************************
*											library/classes/security/errorhandler.php
*                            -------------------
* 	 Copyright (C) 2011
*
* 	 This package is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
* 	 Updated: $Date 2012/02/09 19:00 $
*
************************************************************************/

class errorhandler extends filehandler {
		static $enabled = true;
		static function enable($bool = true) {
				self::$enabled = $bool;
		}

		static function logit($log) {
				$filename = "errorlog_" . @date("d_m_Y") . ".txt";
				$bool     = false;
				if (!parent::isExists($filename, "errorlogs")) $bool = parent::write($filename, $log, "errorlogs");
				if ($bool) return;
				if (parent::checkpermission($filename, 0777, "errorlogs")) $bool = parent::append($filename, $log, "errorlogs");
				if (!$bool)
				{
						ob_clean();
						die("An error has occurred and we have failed to log it, if you have access to the site please chmod the errorlogs folder to 0777 otherwise contact the site administrator");
				}
		}

		static function arraytostring($array) {
				$string = "";
				if (is_array($array) && count($array) < 20)
				{
						$x = 0;
						foreach ($array as $k => $v)
						{
								if ($k == "GLOBALS") continue;
								if (!is_integer($k)) $string .= "\$$k =>";
								if (is_array($v) || is_object($v))
								{
										$string .= "array(" . self::arraytostring((array)$v) . ")";
								}
								else $string .= $v;
								$x++;
								if ($x < count($array)) $string .= ", ";
						}
				}
				return $string;
		}

		static function error($level, $message, $file, $line, $context) {
				if (self::$enabled == false) return;
				switch ($level)
				{
						default:
								self::minor($message, $file, $line, $context);
						break;
						case E_USER_ERROR:
						case E_USER_NOTICE:
						case E_WARNING:
						case E_COMPILE_ERROR:
						case E_CORE_ERROR:
						case E_ERROR:
						case E_ALL:
								self::major($message, $context);
						break;
				}
		}

		static function exception($exception) {
			self::logit($exception->getMessage());
			ob_clean();
			echo "<div align='center'>A critical error has occurred, information regarding your session has been saved, sorry for the inconvience... Please try again shortly.</div>";
			ob_flush();
			exit;
		}

		static function shutdown() {
			$a = error_get_last();
			if($a == null) return; // Normal shutdown
			ob_clean();
			echo "<div align='center'>A critical error has occurred and we are unable to automatically save relevant information to fix this problem, please forward the following onto the website owners...<br><br>".print_r($a, true)."</div>";
			ob_flush();
			return;
		}

		static function ln() {
			$server = strtolower(
			function_exists("php_uname") ? php_uname("s") :
			(isset($_SERVER['OS']) ? $_SERVER['OS'] : "")
			);

			// Windows
			if (strstr($server, 'windows')) return "\r\n";

			// Mac
			if(strstr($server, 'mac')) return "\r";

			return "\n";
		}

		static function minor($message, $file, $line, $args) {
				$ln = self::ln();
				$string = "Function            Source File" . $ln;
				$string .= $message . "(";
				if ($args)
				{
						if (is_array($args))
						{
								$string .= self::arraytostring($args);
						}
						else $string .= $args;
				}
				$string .= ")     " . $file;
				if ($line) $string .= " line " . $line;
				$string .= $ln . $ln . $ln;
				self::logit($string);
		}

		static function major($message = '', $args = '') {
				$ln = self::ln();
				$debug  = @debug_backtrace();
				$string = "Date " . @date("d:m:Y") . " ";
				$string .= "Time " . @date("G:i") . $ln;
				$string .= "========================" . $ln;
				if (isset($message))
				{
						$string .= $ln . "Error Report: " . $message . $ln . $ln;
						if (isset($args) && is_array($args)) $string .= self::arraytostring($args) . $ln;
						$string .= "========================" . $ln;
				}
				$string .= "Call Stack:" . $ln;
				$string .= "Function            Source File" . $ln;
				for ($x=0; $x < count($debug); $x++)
				{
						if (isset($debug[$x]['class']))
							$string .= $debug[$x]['class'];
						if (isset($debug[$x]['type']))
							$string .= $debug[$x]['type'];
						if (isset($debug[$x]['function']))
							$string .= $debug[$x]['function'];
						if (isset($debug[$x]['args']))
						{
								$string .= "( ";
								if (is_array($debug[$x]['args'])) $string .= self::arraytostring($debug[$x]['args']); //$string .= implode(", ", $debug[$x]['args']);
								else $string .= $debug[$x]['args'];
								$string .= " )";
						}
						$string .= "    ";
						if (isset($debug[$x]['file'])) $string .= $debug[$x]['file'];
						if (isset($debug[$x]['line'])) $string .= " line " . $debug[$x]['line'];
						$string .= $ln;
				}
				$string .= $ln . $ln;
				self::logit($string);
				ob_clean();
				echo "<div align='center'>A critical error has occurred, information regarding your session has been saved, sorry for the inconvience... Please try again shortly.</div>";
				ob_flush();
				exit;
		}
}
?>