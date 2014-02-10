<?php
include_once ('common/TorrentFinderBase.php');
include_once ('common/TorrentData.php');

class CorsaroNeroTorrentFinder extends TorrentFinderBase {

	public function getSiteUrl(){
		return "http://ilcorsaronero.info";
	}


	public function getSourceName() {
		return "ilCorSaRoNeRo";
	}

	public function getFgAndBgColor() {
		$color['fg'] = 'white';
		$color['bg'] = 'black';
		return $color;
	}

	public function getBaseUrl() {
		return 'http://ilcorsaronero.info/argh.php?';
	}

	protected function getRequestParameters($keywords) {

		return http_build_query(array('search' => $keywords));
	}

	public function getRequestContext($keywords) {
		$options = array('http' => array('header' => "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n", 'method' => 'GET', ), );
		return stream_context_create($options);
	}

	/*select the portion of html where links to torrents are.*/
	protected function getTargetNodeList($html) {
		$doc = new DOMDocument;
		$doc -> loadHTML($html);

		$xpath = new DOMXPath($doc);
		//*[@id="left"]/table/tbody/tr/td[2]/table/tbody/tr[3]
		//*[@id="left"]/table/tbody/tr/td[2]/table/tbody/tr[3]
		$query = '//*[@id="left"]/table/tr/td[2]/table/tr[@class="odd"] | //*[@id="left"]/table/tr/td[2]/table/tr[@class="odd2"] ';
		$result = $xpath -> query($query);

		return $result;
	}

	protected function parseDomNode($node) {
		$tdList = $node -> childNodes;

		$name = $tdList -> item(2) -> firstChild -> nodeValue;
		$sourceUrl = $tdList -> item(2) -> firstChild -> getAttribute('href');

		$category = $tdList -> item(0) -> nodeValue;
		$size = $tdList -> item(3) -> nodeValue;
		$description = $category . " - " . $size;

		$downloadBaseUrl = "http://torcache.net/torrent/";
		$paramValue = $tdList -> item(5) -> firstChild -> firstChild -> getAttribute('value');

		$torrentLink = $downloadBaseUrl . $paramValue . ".torrent";
		$magnetLink = "magnet:?xt=urn:btih:" . $paramValue;

		if ($torrentLink != null || $magnetLink != null) {
			$torrent = new TorrentData($torrentLink, $magnetLink, $name, $description, $sourceUrl);
		}

		return $torrent;
	}

}
?>