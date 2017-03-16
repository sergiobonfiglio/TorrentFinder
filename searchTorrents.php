<?php
$basePath = './parsers';

$keywords = $_POST["keywords"];
$provider = $_POST["p"];

if (isset($keywords) && isset($provider) && $keywords != "") {

	include_once ("$basePath/$provider.php");
	$finder = new $provider();

	$res = $finder -> findTorrents($keywords);

	if (isset($results) && $res != null) {
		$results = array_merge($results, $res);
	} else {
		$results = $res;
	}

	echo json_encode($results);
} else {
	return null;
}
?>

