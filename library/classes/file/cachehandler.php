<?php
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
