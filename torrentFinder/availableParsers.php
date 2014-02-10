<?php
class SerializedParser {

	public function __construct($name, $fgColor, $bgColor, $url, $className) {
		$this -> name = $name;
		$this -> fgColor = $fgColor;
		$this -> bgColor = $bgColor;
		$this -> url = $url;
		$this->className = $className;
	}

	public $name;
	public $bgColor;
	public $fgColor;

	public $className;
	public $url;
}

function getParsers() {

	$basePath = './parsers';
	$i = 0;
	if ($handle = opendir($basePath)) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != ".." && stripos($entry, '.php') !== false) {

				include_once ("$basePath/" . $entry);
				$className = str_ireplace('.php', '', $entry);
				$obj = new $className();
				$colors = $obj -> getFgAndBgColor();
				$parsers[$i] = new SerializedParser($obj -> getSourceName(), $colors['fg'], $colors['bg'], $obj -> getSiteUrl(), $className);
				$i++;
			}
		}
		closedir($handle);

		return json_encode($parsers);
	}

}

echo getParsers();
?>