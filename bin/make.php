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

$assetgraph = array();

$assets[$source] = getAssetsFromHtml($source);

print_r($schemes); echo "<br>";
echo "<br><pre>";print_r($assets); echo "</pre><br>";
echo "<br><pre>";print_r($assetgraph); echo "</pre><br>";

$js = getCompiledJs();
echo strlen($js);
echo "<br><pre>"; print_r($js); echo "</pre><br>";

mkdir($sourcedir . "build/");
file_put_contents($sourcedir . "build/shh.js", iconv("ISO-8859-1", "UTF-8//IGNORE", str_replace("\r", "", $js)));


function getAssetsFromHtml($source) {
	global $sourcedir, $schemes, $assetgraph, $prefixesstr;
	
	$assets = array();
	
	$lines = file($source);
	$content = implode("", $lines);
	$content = str_replace("\n", "", $content);
	
	// get prefixes/schemes
	preg_match_all("/shh.src\\s*=\\s*{\\s*([^}]*)\\s*}\\s*;+/m", $content, $matches);
	$prefixesstr = $matches[0][0];
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
		$source = $sourcedir . $asset;
		if (!$assetgraph[$asset]) {
			$assets[$asset] = getAssetsFromJs($source);
			$assetgraph[$asset] = true;
		}
	}
	
	return $assets;
}


function getAssetsFromJs($source) {
	global $sourcedir, $schemes, $assetgraph;
	
	$assets = array();
	
	$lines = file($source);
	$content = implode("", $lines);
	$content = str_replace("\n", "", $content);
	
	// get from requires
	$requirerx = "shh.require\\s*\\(\\s*[\"']([^\"']*)[\"']\\)";
	
	// get from class extend
	$extendrx = "extend\\s*:\\s*[\"']([^\"']*)[\"']";
	
	// get from module/class require
	$requireattrx = "require\\s*:\\s*[\"']([^\"']*)[\"']";
	
	// get from module/class require
	$requireattarrrx = "require\\s*:\\s*\\[\\s*[\"']([^\\]]*)[\"']\\s*\\]";
	
	$jsrx = "/($requirerx|$extendrx|$requireattrx|$requireattarrrx)/i";
	
	preg_match_all($jsrx, $content, $matches);
	$jlen = count($matches[2]);
	for($j = 0; $j < $jlen; $j += 1) {
		$asset = $matches[2][$j];
		if (!$asset) {
			$asset = $matches[3][$j];
		}
		if (!$asset) {
			$asset = $matches[4][$j];
		}
		if (!$asset) {
			$assetsarr = preg_split("/[\"']\\s*,\\s*[\"']/", $matches[5][$j]);
			$klen = count($assetsarr);
			for ($k = 0; $k < $klen; $k += 1) {
				$asset = $assetsarr[$k];
				echo $asset;
				$assetarr = explode(":", $asset);
				$asset = count($assetarr) > 1 ? $schemes[$assetarr[0]] . $assetarr[1] : $asset;
				if (!strstr($asset, "/")) {
					$assetarr = explode(".", $asset);
					$asset = $schemes[array_shift($assetarr)] . implode("/", $assetarr) . ".js";
				}
				$source = $sourcedir . $asset;
				if (!$assetgraph[$asset]) {
					$assets[$asset] = getAssetsFromJs($source);
					$assetgraph[$asset] = true;
				}
			}
		}
		$assetarr = explode(":", $asset);
		$asset = count($assetarr) > 1 ? $schemes[$assetarr[0]] . $assetarr[1] : $asset;
		if (!strstr($asset, "/")) {
			$assetarr = explode(".", $asset);
			$asset = $schemes[array_shift($assetarr)] . implode("/", $assetarr) . ".js";
		}
		$source = $sourcedir . $asset;
		if (!$assetgraph[$asset]) {
			$assets[$asset] = getAssetsFromJs($source);
			$assetgraph[$asset] = true;
		}
	}
	
	return $assets;
}

function getCompiledJs() {
	global $sourcedir, $assetgraph, $prefixesstr;
	
	$js = "";
	
	foreach($assetgraph as $asset=>$value) {
		$type = strtolower(array_pop(explode(".", $asset)));
		if ($type == "js") {
			$lines = file($sourcedir . $asset);
			$content = implode("", $lines);
			if (mb_detect_encoding($content, 'UTF-8', true)) {
				$content = iconv("UTF-8", "ISO-8859-1//IGNORE", $content);
			}
			$js .= $content . "\n";
			if (strstr($asset, "shahx.js")) {
				$js .= "$prefixesstr\n\n";
				$jsassets = "";
				foreach($assetgraph as $asset2=>$value2) {
					$jsassets .= ($jsassets ? ",\n" : "") . "    '$asset2'";
				}
				$js .= "shh.markLoaded([\n$jsassets\n]);\n\n";
			}
		}
	}
	
	return $js;
}
?>