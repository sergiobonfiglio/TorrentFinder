<?php
include_once ('common/TorrentFinderBase.php');
include_once ('common/TorrentData.php');

class PirateBayTorrentFinder extends TorrentFinderBase {

	protected $siteUrl = "https://thepiratebay.org";
	
	
	
	public function getSiteUrl(){
		return $this->siteUrl;
	}

	public function getSourceName() {
		return "ThePirateBay";
	}
	
	public function getFgAndBgColor() {
		$color['fg'] = '#B78020';
		$color['bg'] = 'black';
		return $color;
	}

	public function getBaseUrl() {
		return $this->getSiteUrl().'/search/';
	}
	protected function getParamEncodedUrl($keywords) {
		return 
			$this -> getBaseUrl() 
			. $this -> getRequestParameters($keywords)
			. '/0/5/0' // order by Size DESC
		;
	}

	protected function getRequestParameters($keywords) {
		return urlencode($keywords);
	}

	public function getRequestContext($keywords) {
		$options = array('http' => array('method' => 'GET', ));
		return stream_context_create($options);
	}

	/*select the portion of html where links to torrents are.*/
	protected function getTargetNodeList($html) {
		$doc = new DOMDocument;
		$doc -> loadHTML($html);

		$xpath = new DOMXPath($doc);
		//*[@id="searchResult"]/tbody/tr[1]
		$query = '//*[@id="searchResult"]/tr';
		$result = $xpath -> query($query);

		return $result;
	}

	protected function parseDomNode($node) {

		$tdList = $node -> childNodes;
		$contentTd = $tdList -> item(2) -> childNodes;

		$name = $contentTd -> item(1) -> nodeValue;

		$description = $contentTd -> item(8) -> nodeValue;
		if ($description == null)
			$description = "";

		$sourceUrl = $this->getSiteUrl() . $contentTd -> item(1) -> childNodes -> item(1) -> getAttribute('href');

		$torrentLink = '';
		$magnetLink = $contentTd -> item(3) -> getAttribute('href');

		if ($torrentLink != null || $magnetLink != null) {
			$torrent = new TorrentData($torrentLink, $magnetLink, $name, $description, $sourceUrl);
		}

		return $torrent;
	}

}
