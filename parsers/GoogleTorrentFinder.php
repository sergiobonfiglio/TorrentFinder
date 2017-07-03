<?php
include_once ('common/TorrentFinderBase.php');
include_once ('common/TorrentData.php');

class GoogleTorrentFinder extends TorrentFinderBase {

	private $maxBreadth = 5;
	private $counter = 0;
	private $torrentList;

	public function getSiteUrl(){
		return "http://www.google.com";
	}

	public function getSourceName() {
		return "Google";
	}

	public function getFgAndBgColor() {
		$color['fg'] = 'white';
		$color['bg'] = 'royalblue';
		return $color;
	}

	protected function getRequestParameters($keywords) {
		$addedKeywords = "torrent";
		if (stripos($keywords, $addedKeywords)) {
			$keywords = str_replace($addedKeywords, "", $keywords);
		}
		return http_build_query(array('q' => $keywords . ' ' . $addedKeywords));
	}

	protected function getBaseUrl() {
		return "http://www.google.com/search?";
	}

	protected function getRequestContext($keywords) {

		$options = array('http' => array('method' => 'GET'));
		return stream_context_create($options);
	}

	/*select the portion of html where links to torrents are.*/
	protected function getTargetNodeList($html) {
		////*[@id="rso"]/li[1]/div/h3/a
		$query = '//*[@id="ires"]/ol/li/h3/a';
		return $this -> genericGetTargetNodeList($html, $query);
	}

	protected function parseDomNode($node) {

		if ($this -> counter < $this -> maxBreadth) {
			$this -> counter += 1;
			$href = $node -> getAttribute('href');
			$rawUrl = $this -> getSubstring($href, 0, "/url?q=", "&", false);
			$url = urldecode($rawUrl['substring']);

			$firstResult = file_get_contents($url);
			if ($firstResult != null) {

				$nodeList = $this -> genericGetTargetNodeList($firstResult, '//body//a[@href]');
				foreach ($nodeList as $node2) {
					$link = $node2 -> getAttribute('href');
					if (Link::getType($link) == Link::Magnet) {
						if (!isset($torrentList[$link])) {
							$displayName = Link::getDisplayName($link);
							$torrent = new TorrentData(null, $link, $displayName, $node -> nodeValue, $url);
							$torrentList[$link] = $torrent;
						}
					} elseif (Link::getType($link) == Link::Torrent) {
						/*
						 //disabled as it doesn't get reliable results
						 if (isset($torrentList[$link])) {
						 $torrentList[$link] -> torrentLink = $link;
						 } else {
						 $torrent = new TorrentData($link, null, $node -> nodeValue, $node2 -> nodeValue, $url);
						 $torrentList[$link] = $torrent;
						 }
						 */
					}
				}

			}
			return array_values($torrentList);
		} else {
			return null;
		}
	}

	public function findTorrents($keywords) {
		if (strlen($keywords) > 0) {
			return parent::findTorrents($keywords);
		} else {
			return null;
		}
	}

	private function getSubstring($string, $startPosition, $startsWith, $endsWith, $includeDelimiters = true) {

		$strStart = stripos($string, $startsWith, $startPosition);

		if ($strStart === false) {
			return null;
		} else {

			$strEnd = strpos($string, $endsWith, $strStart + strlen($startsWith));
			if ($strEnd != null) {

				if ($includeDelimiters == FALSE) {
					$strStart = $strStart + strlen($startsWith);
					$strEnd = $strEnd - strlen($endsWith);
				}

				return array("substring" => substr($string, $strStart, $strEnd - $strStart + 1), "position" => $strStart);
			} else {
				return null;
			}
		}
	}

}
?>