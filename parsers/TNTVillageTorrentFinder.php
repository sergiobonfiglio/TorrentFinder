<?php
include_once ('common/TorrentFinderBase.php');
include_once ('common/TorrentData.php');

class TNTVillageTorrentFinder extends TorrentFinderBase {

	public function getSiteUrl() {
		return "http://www.tntvillage.scambioetico.org";
	}

	public function getSourceName() {
		return "TNT Village";
	}

	public function getFgAndBgColor() {
		$color['fg'] = 'white';
		$color['bg'] = 'orange';
		return $color;
	}

	public function getBaseUrl() {
		return 'http://www.tntvillage.scambioetico.org/src/releaselist.php';
	}

	public function getRequestContext($keywords) {
		$data = array('cat' => '0', 'page' => '1', 'srcrel' => $keywords);
		// use key 'http' even if you send the request to https://...
		$options = array('http' => array('header' => "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n", 'method' => 'POST', 'content' => http_build_query($data), ), );
		return stream_context_create($options);
	}

	/*select the portion of html where links to torrents are.*/
	protected function getTargetNodeList($html) {
		$doc = new DOMDocument;
		$doc -> loadHTML($html);

		$xpath = new DOMXPath($doc);

		// //*[@id="paging_box"]/div[2]/table/tbody/tr[2]
		$query = '//div/table/tr';
		$result = $xpath -> query($query);

		return $result;
	}

	protected function parseDomNode($node) {
		$tdList = $node -> childNodes;

		if (get_class($tdList -> item(0) -> firstChild) == "DOMElement") {

			$torrentLink = $tdList -> item(0) -> firstChild -> getAttribute('href');
			$magnetLink = $tdList -> item(1) -> firstChild -> getAttribute('href');
			$sourceUrl = $tdList -> item(6) -> firstChild -> getAttribute('href');
			$name = $tdList -> item(6) -> firstChild -> nodeValue;
			$description = $tdList -> item(6) -> nodeValue;

			if ($torrentLink != null || $magnetLink != null) {
				$torrent = new TorrentData();
				$torrent -> torrentLink = $torrentLink;
				$torrent -> magnetLink = $magnetLink;
				$torrent -> name = $name;
				$torrent -> description = $description;
				$torrent -> sourceUrl = $sourceUrl;

				return $torrent;
			} else {
				return null;
			}
		} else {
			return null;
		}

	}

}
?>