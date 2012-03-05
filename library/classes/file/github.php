<?php

class github {
	var $gitapiurl = "https://api.github.com/";
	var $username = "Swiftsmoke";
	var $repo = "openwowcms";

	function objectCheck($json, $array = false)
	{
		if (is_object($json) || is_array($json))
			return $json;

		$json = new stdClass;
		$json->sha = SHA_VERSION;
		if ($array)
			return array($json);
		return $json;
	}

	# StdObject(
	#		url
	#		committer(url, gravatar_id, login, id, avatar_url)
	#		commit(url, committer(email, date, name), message, author(email, date, name), tree(url, sha))
	#		author(url, gravatar_id, login, id, avatar_url)
	#		parents(url, sha)
	#		sha
	# );
	function get_last_commit() {
		$json = $this->commits_request();
		$array = json_decode($json);

		return $this->objectCheck($array[0]);
	}

	function get_commits() {
		return $this->objectCheck(json_decode($this->commits_request()), true);
	}

	function get_commit($sha) {
		return $this->objectCheck(json_decode($this->commit_request($sha)));
	}

	function create_link($sha, $color = null) {
		return "<a href='https://github.com/Swiftsmoke/openwowcms/commit/$sha' target='_blank'".($color == null ? "" : "style='color:$color'").">".substr($sha, 0, 10)."</a>";
	}

	function get_file($sha, $filename) {
		$url = "https://raw.github.com/{$this->username}/{$this->repo}/{$sha}/{$filename}";
		$content = "";
		if (function_exists("curl_init"))
			$content = $this->curl($url);
		else if (function_exists("file_get_contents"))
			$content = $this->filecontents($url);
		return filehandler::write($filename, $content);
	}

	function commit_request($sha) {
		$url = $this->gitapiurl."repos/{$this->username}/{$this->repo}/commits/$sha";
		if (function_exists("curl_init"))
			return $this->curl($url);

		if (function_exists("file_get_contents"))
			return $this->filecontents($url);
	}

	function commits_request() {
		$url = $this->gitapiurl."repos/{$this->username}/{$this->repo}/commits";
		if (function_exists("curl_init"))
			return $this->curl($url);

		if (function_exists("file_get_contents"))
			return $this->filecontents($url);
	}

	function curl($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

		$content = curl_exec($curl);
		curl_close($curl);
		return $content;
	}

	function filecontents($url) {
		$w = stream_get_wrappers();
		if (!extension_loaded('openssl') || !in_array('https', $w))
			return null;

		$jsonHeader = stream_context_create(array('https'=>array(
				'header'=>"Content-type: application/json",
				'method'=>'GET')));

		return file_get_contents($url, 0, $jsonHeader);
	}
}
?>