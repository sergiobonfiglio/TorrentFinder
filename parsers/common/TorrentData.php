<?php
class TorrentData {

	public function __construct($torrentLink, $magnetLink, $name, $description, $sourceUrl) {
		$this ->torrentLink = $torrentLink;
		$this->magnetLink = $magnetLink;
		$this->name = $name;
		$this->description = $description;
		$this->sourceUrl = $sourceUrl;
	}

	public $torrentLink;
	public $magnetLink;

	public $name;
	public $description;
	public $sourceUrl;
	public $source;
	public $bgColor;
	public $fgColor;
	
	public $seeders;
	public $leechers;
	public $size;

}
?>