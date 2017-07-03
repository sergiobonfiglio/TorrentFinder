<?php
session_start();

class Arguments {
	public $filename;
}

class TorrentAddRequest {

	public $arguments;
	public $method = "torrent-add";

	public function __construct($link, $isTorrent) {
		if ($isTorrent == true) {
			$this -> arguments['metainfo'] = base64_encode($link);
		} else {
			$this -> arguments['filename'] = $link;
		}
	}

}

function postRequest($url, $data, $header) {

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	$output = curl_exec($ch);

	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

	curl_close($ch);

	// Then, after your curl_exec call:
	$response['header'] = substr($output, 0, $header_size);
	$response['body'] = substr($output, $header_size);
	return $response;
}

$link = $_POST['link'];
$type = $_POST['type'];

if (isset($link) && isset($type)) {

	$url = 'http://raspberrypi:5555/transmission/rpc';
	$header = array();
	$header[] = "X-Transmission-Session-Id: " . $_SESSION['transmission-session'];

	if ($type == "torrent") {
		//it's a torrent file: it must be first downloaded
		$fileContent = file_get_contents($link);

		$finfo = new finfo(FILEINFO_MIME);
		$mimeType = $finfo -> buffer($fileContent);
		echo $mimeType;
		if (strpos($mimeType, "application/x-gzip") !== FALSE) {
			$fileContent = gzdecode($fileContent);
		} elseif (strpos($mimeType, "application/x-bittorrent") === FALSE) {
			//unhandled mime-type
			echo "Error: unhandled mime-type [" + $mimeType + "]";
			//throw new Exception("Error: unhandled mime-type [" + $mimeType + "]");
			echo $fileContent;
		}

		$request = json_encode(new TorrentAddRequest($fileContent, true));
	} else {
		$request = json_encode(new TorrentAddRequest($link, false));
	}

	//send request
	$response = postRequest($url, $request, $header);

	//read header to change session id if necessary
	if (stripos($response['header'], "HTTP/1.1 409 Conflict") !== false) {
		$rows = explode("\n", $response['header']);
		$found = false;
		for ($i = 0; $i < count($rows) && $found == false; $i++) {
			$row = $rows[$i];

			$chunks = explode(":", $row);
			if ($chunks[0] == "X-Transmission-Session-Id") {
				$sessionId = trim($chunks[1]);
				$_SESSION['transmission-session'] = $sessionId;
				$header[] = "X-Transmission-Session-Id: $sessionId";
				$found = true;
			}
		}

		//repeat request with new session id
		if (isset($sessionId)) {
			$response = postRequest($url, $request, $header);
		}
	}

	//echo $response['header'];
	echo $response['body'];
}
?>