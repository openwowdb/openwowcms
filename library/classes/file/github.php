<?php
class github {
	var $gitapiurl = "https://api.github.com/";
	var $username = "Swiftsmoke";
	var $repo = "openwowcms";

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

		return $array[0];
	}

	function get_commits() {
		return json_decode($this->commits_request());
	}

	function get_commit($sha) {
		return json_decode($this->commit_request($sha));
	}

	function create_link($sha) {
		return "<a href='https://github.com/Swiftsmoke/openwowcms/commit/$sha' target='_blank'>".substr($sha, 0, 10)."</a>";
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

		$content = curl_exec($curl);
		curl_close($curl);
		return $content;
	}

	function filecontents($url) {
		// Send as json
		$jsonHeader = stream_context_create(array('http'=>array(
				'header'=>"Content-type: application/json",
				'method'=>'GET')));

		return file_get_contents($url, 0, $jsonHeader);
	}
}
?>