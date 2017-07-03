<?php
include_once ('common/TorrentFinderBase.php');
include_once ('common/TorrentData.php');

class KickAssTorrentFinder extends TorrentFinderBase {

	public function getSiteUrl() {
		return "http://kickass.to";
	}

	public function getSourceName() {
		return "KickAss";
	}

	public function getFgAndBgColor() {
		$color['fg'] = '#FFF1A6';
		$color['bg'] = '#6F613F';
		return $color;
	}

	public function getBaseUrl() {
		return 'http://kickass.to/usearch/';
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
		//table[@class="data"]/tr[@class="odd"] | //table[@class="data"]/tr[@class="even"]
		$query = '//table[@class="data"]/tr[@class="odd"] | //table[@class="data"]/tr[@class="even"]';
		$result = $xpath -> query($query);

		return $result;
	}

	public function printNode($node, $level, $item) {
		echo "\n";
		for ($i = 0; $i < $level; $i++) {
			echo "\t";
		}
		echo "<$node->nodeName [$item]";
		if ($node -> nodeType != XML_TEXT_NODE && $node -> hasAttribute('class'))
			echo " class=" . $node -> getAttribute('class');
		if ($node -> nodeType != XML_TEXT_NODE && $node -> hasAttribute('href'))
			echo " href=" . $node -> getAttribute('href');
		echo ">\n";

		//recursion
		if ($node -> hasChildNodes()) {
			$i = 0;
			foreach ($node->childNodes as $child) {
				$this -> printNode($child, $level + 1, $i);
				$i++;
			}
		} else {
			echo "$node->nodeValue";
		}

		echo "</$node->nodeName>";
	}

	protected function parseDomNode($node) {
		$node -> normalize();
		$tdList = $node -> childNodes;

		$firstColumn = $tdList -> item(0);


		$this -> printNode($firstColumn -> childNodes -> item(1), 0, 0);
		echo "\n======================\n";

		$magnet = $firstColumn -> childNodes -> item(1)/*div1*/ -> childNodes -> item(7) -> getAttribute('href');
		$torrent = $firstColumn -> childNodes -> item(1)/*div1*/ -> childNodes -> item(9) -> getAttribute('href');

		$secondDiv = $firstColumn -> childNodes -> item(3);
		$name = $secondDiv -> childNodes -> item(3) -> nodeValue;

		if ($torrentLink != null || $magnetLink != null) {
			$torrent = new TorrentData($torrentLink, $magnetLink, $name, $description, $sourceUrl);
		}

		return $torrent;
	}

}
?>