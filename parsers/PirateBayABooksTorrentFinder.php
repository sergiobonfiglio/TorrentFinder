<?php
require_once ('common/TorrentFinderBase.php');
require_once ('common/TorrentData.php');
require_once ('PirateBayTorrentFinder.php');

class PirateBayABooksTorrentFinder extends PirateBayTorrentFinder {

	public function getSourceName() {
		return "ThePirateBay: aBooks";
	}
	
	protected function getParamEncodedUrl($keywords) {
		$url = 
			$this -> getBaseUrl() 
			. $this -> getRequestParameters($keywords)
			. '/0/99/102' // order by Size DESC
		;
		
		return $url;
	}
	
}
