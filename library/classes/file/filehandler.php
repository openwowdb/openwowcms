<?php
/************************************************************************
*											library/classes/file/filehandler.php
*                            -------------------
* 	 Copyright (C) 2011
*
* 	 This package is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
* 	 Updated: $Date 2012/02/08 14:00 $
*
************************************************************************/
class filehandler {
	static function getFilename($filename, $directory = null) {
		if ($directory) $filename = PATHROOT . $directory . "/" . $filename;
		else $filename = PATHROOT . $filename;
		return $filename;
	}

	static function isExists($filename, $directory = null) { return file_exists(self::getFilename($filename, $directory)); }

	static function delete($filename, $directory = null) { unlink(self::getFilename($filename, $directory)); }

	static function file($filename, $directory = null) {
		$filename = self::getFilename($filename, $directory);
		return @file($filename);
	}

	static function read($filename, $directory = null) {
		$filename = self::getFilename($filename, $directory);

		if (!is_readable($filename))
			return false;

		@$fd = fopen($filename, "r");

		if (!$fd) return false;

		$buffer = "";

		while (!feof($fd)) $buffer .= fgets($fd, 4096);

		fclose ($fd);
		return $buffer;
	}

	static function getModificationTime($filename, $directory = null) { return filemtime(self::getFilename($filename, $directory)); }

	static function checkpermission($filename, $needed = 0999, $directory = null) {
		$filename = self::getFilename($filename, $directory);
		$chmod    = substr(sprintf('%o', fileperms($filename)), -4);

		if ($needed == $chmod)
			return true;

		$try = chmod($filename, $needed);

		if ($try)
			return true;

		return false;
	}

	static function append($filename, $content, $directory = null) {
		$filename = self::getFilename($filename, $directory);

		$fd = fopen($filename, "a+");
		if (!$fd)
			return false;
		fwrite($fd, $content);
		fclose($fd);
		return true;
	}

	static function write($filename, $content, $directory = null) {
		$filename = self::getFilename($filename, $directory);

		$fd = fopen($filename, "w");
		if (!$fd)
			return false;

		fwrite($fd, $content);
		fclose($fd );
		return true;
	}

	static function getDir($directory = "", $get_dirs = false, $mask = null) {
		$filename = self::getFilename("", $directory);

		if ($handle = opendir($filename)) {
			while (false !== ($file = readdir($handle))) {
				if (!preg_match("/^\/./", $file)) {
					if ($get_dirs) {
						if (is_dir($filename . "/" . $file)) {
							if (($mask and (preg_match ($mask, $file))) or (!$mask)) { $files[] = $file; }
						}
					}
					else {
						if (!is_dir($filename . "/" . $file)) {
							if (($mask and (preg_match ($mask, $file))) or (!$mask)) { $files[] = $file; }
						}
					}
				}
			}
			closedir($handle);
		}

		if (isset($files)) {
			sort($files);
			return $files;
		}
		else return false;
	}

	static function saveHttp($filename, $http_name, $directory = null) {
		$filename = self::getFilename($filename, $directory);

		if (self::isExists($filename))
			return false;

		copy($_FILES[$http_name]["tmp_name"], $filename);
		return true;
	}

	static function makeDir($filename, $directory = null) { mkdir(self::getFilename($filename, $directory), 0777); }

	static function removeDir($filename, $directory = null) {
		$filename = self::getFilename($filename, $directory);
		$content  = self::getDir($filename);

		foreach ($content as $fn) { self::delete($fn, $filename); }

		rmdir($filename);
	}

	static function renameDir($old_filename, $new_filename, $directory = null) {
		$old_filename = self::getFilename($old_filename, $directory);
		$new_filename = self::getFilename($new_filename, $directory);
		rename($old_filename, $new_filename);
	}
}
?>
