<?php
/************************************************************************
*											library/classes/file/cachehandler.php
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
class cachehandler extends filehandler {
	static $cache_dir = "engine/_cache";
	static function isCached($file) {
		// File Exists?
		if (!self::isExists($file, self::$cache_dir))
			return false;

		// 30 mins ago - expired cache
		//if (self::getModificationTime($file, self::$cache_dir) < (time() - 1800)) return false;
		return true;
	}

	static function loadCache($file) {
		if (!self::isCached($file))
			return false;
		$filename = self::getFilename($file, self::$cache_dir);
		include $filename;
		return true;
	}
}
?>
