Enter the relative destination of the root page (usually index.html).<br>
<form method="get">
<input type="text" name="source" value="../../../../index.html">
<input type="submit">
</form>
<?php
$source = $_GET["source"];

$sourcearr = explode("/", $source);
array_pop($sourcearr);
$sourcedir = count($sourcearr) ? implode("/", $sourcearr) . "/" : "";
echo $sourcedir; echo "<br>";

$assets = array();
$schemes = array();

$assets[$source] = getAssetsFromHtml($source, $schemes);

print_r($schemes); echo "<br>";
print_r($assets); echo "<br>";

function getAssetsFromHtml($source) {
	global $sourcedir, $schemes;
	
	$assets = array();
	
	$lines = file($source);
	$content = implode("", $lines);
	$content = str_replace("\n", "", $content);
	
	// get prefixes/schemes
	preg_match_all("/shh.src\\s*=\\s*{\\s*([^}]*)\\s*}/m", $content, $matches);
	$jlen = count($matches[1]);
	for($j = 0; $j < $jlen; $j += 1) {
		$schemestr = $matches[1][$j];
		$schemearr = preg_split("/\\s*,\\s*/", $schemestr);
		$klen = count($schemearr);
		for($k = 0; $k < $klen; $k += 1) {
			$schemearrarr = preg_split("/\\s*:\\s*/", $schemearr[$k]);
			$schemes[$schemearrarr[0]] = trim(preg_replace("/[\"']/", "", $schemearrarr[1]));
		}
	}
	
	// get from script tags
	$scriptrx = "src\\s*=\\s*[\"']([^\"']*)[\"']";
	
	// get from requires
	$requirerx = "shh.require\\s*\\(\\s*[\"']([^\"']*)[\"']\\)";
	
	$jsrx = "/($scriptrx|$requirerx)/i";
	
	preg_match_all($jsrx, $content, $matches);
	$jlen = count($matches[2]);
	for($j = 0; $j < $jlen; $j += 1) {
		$asset = $matches[2][$j];
		if (!$asset) {
			$asset = $matches[3][$j];
			$assetarr = explode(":", $asset);
			$asset = count($assetarr) > 1 ? $schemes[$assetarr[0]] . $assetarr[1] : $asset;
		}
		$assets[$sourcedir . $asset] = true;
	}
	
	return $assets;
}
?>