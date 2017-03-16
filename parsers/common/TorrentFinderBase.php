<?php
include_once ('ITorrentFinder.php');

abstract class Link {

	const Magnet = 0;
	const Torrent = 1;
	const Unknown = 2;

	public static function getType($link) {
		if (self::startsWith($link, "magnet:?")) {
			return self::Magnet;
		} elseif (self::endsWith($link, ".torrent")) {
			return self::Torrent;
		} else {
			return self::Unknown;
		}
	}

	public static function getDisplayName($link) {
		if (self::getType($link) == self::Magnet) {
			$params = explode("&", $link);
			foreach ($params as $p) {
				list($key, $value) = explode("=", $p);
				if ($key == "dn")
					return urldecode($value);
			}
		}
		return null;
	}

	private static function startsWith($haystack, $needle) {
		return strpos($haystack, $needle) === 0;
	}

	private static function endsWith($haystack, $needle) {
		return substr($haystack, -strlen($needle)) === $needle;
	}

}

abstract class TorrentFinderBase implements ITorrentFinder {

	private $lastKeywords;
	private $lastHaystack;

	public abstract function getSiteUrl();
	public abstract function getSourceName();
	protected abstract function getBaseUrl();
	public abstract function getFgAndBgColor();

	private function getParamEncodedUrl($keywords) {
		return $this -> getBaseUrl() . $this -> getRequestParameters($keywords);
	}

	protected function getRequestParameters($keywords) {
		return $keywords;
	}

	protected abstract function getRequestContext($keywords);

	protected abstract function parseDomNode($node);

	private function getHtmlPage($keywords) {

		if (!isset($lastKeywords) || $keywords != $lastKeywords) {
			$context = $this -> getRequestContext($keywords);
			$options = stream_context_get_options($context);

			if ($options['http']['method'] == "POST") {
				$url = $this -> getBaseUrl();
			} else {
				$url = $this -> getParamEncodedUrl($keywords);
			}

			$html = file_get_contents($url, false, $context);

			$finfo = new finfo(FILEINFO_MIME);
			$mimeType = $finfo -> buffer($html);

			if (strpos($mimeType, "application/x-gzip") !== FALSE) {
				$html = gzdecode($html);
			}

		}
		return $html;
	}

	/*select the portion of html where links to torrents are.*/
	protected function getTargetNodeList($html) {
		$query = '/';
		return $this -> genericGetTargetNodeList($html, $query);
	}

	protected function genericGetTargetNodeList($html, $query) {

		$doc = new DOMDocument;
		$doc -> loadHTML($html);

		$xpath = new DOMXPath($doc);

		$result = $xpath -> query($query);

		return $result;
	}

	public function findTorrents($keywords) {
		$htmlPage = $this -> getHtmlPage($keywords);
		$nodes = $this -> getTargetNodeList($htmlPage);

		$i = 0;
		foreach ($nodes as $node) {
			$foundTorrents = $this -> parseDomNode($node);
			if ($foundTorrents != null) {
				if (!is_array($foundTorrents)) {
					$foundTorrents = array($foundTorrents);
				}
				foreach ($foundTorrents as $torrent) {
					$torrent -> source = $this -> getSourceName();

					$color = $this -> getFgAndBgColor();
					$torrent -> fgColor = $color['fg'];
					$torrent -> bgColor = $color['bg'];

					$torrentList[$i] = $torrent;
					$i++;
				}
			}
		}

		return $torrentList;
	}

}
?>