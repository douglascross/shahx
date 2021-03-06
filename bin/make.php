<?php
$source = array_key_exists("source", $_GET) ? $_GET["source"] : null;
$destjs = array_key_exists("destjs", $_GET) ? $_GET["destjs"] : null;
$exjs = array_key_exists("exjs", $_GET) ? $_GET["exjs"] : null;
?>
<form method="get">
<label>Source file</label><input type="text" name="source" value="<?php echo isset($source) ? $source : "index.html"; ?>"><br>
<label>Destination js file</label><input type="text" name="destjs" value="<?php echo isset($destjs) ? $destjs : "shh.js"; ?>"><br>
<label>Exclude js files</label><input type="text" name="exjs" value="<?php echo isset($exjs) ? $exjs : ""; ?>"><br>
<input type="submit">
</form>
<?php
if($source) {
	
	$root = "../../";
	
	$source = $root . $source;
	$destjs = $root . $destjs;
	
	$sourcearr = explode("/", $source);
	array_pop($sourcearr);
	$sourcedir = count($sourcearr) ? implode("/", $sourcearr) . "/" : "";
	
	$assets = array();
	$schemes = array();
	
	$assetgraph = array();
	
	$assets[$source] = getAssetsFromHtml($source);
	
	//print_r($schemes); echo "<br>";
	//echo "<br><pre>";print_r($assets); echo "</pre><br>";
	echo "<br><pre>";print_r($assetgraph); echo "</pre><br>";
	
	$js = getCompiledJs();
	$js = str_replace("\r", "\n", str_replace("\r\n", "\n", $js));
	echo round(strlen($js) / 1024) . " kB<br>";
	//echo "<br><pre>"; print_r($js); echo "</pre><br>";
	
	if (mb_detect_encoding($js, 'UTF-8', true)) {
		file_put_contents($destjs, $js);
		echo "Build succeeded";
	} else {
		echo "Build failed! - not UTF-8 encoded";
	}
}


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
		if (!array_key_exists($asset, $assetgraph)) {
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
				$assetarr = explode(":", $asset);
				$asset = count($assetarr) > 1 ? $schemes[$assetarr[0]] . $assetarr[1] : $asset;
				if (!strstr($asset, "/")) {
					$assetarr = explode(".", $asset);
					$asset = $schemes[array_shift($assetarr)] . implode("/", $assetarr) . ".js";
				}
				$source = $sourcedir . $asset;
				if (!array_key_exists($asset, $assetgraph)) {
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
		if (!array_key_exists($asset, $assetgraph)) {
			$assets[$asset] = getAssetsFromJs($source);
			$assetgraph[$asset] = true;
		}
	}
	
	return $assets;
}

function inExcludeList($asset) {
	global $exjs;
	$exjsarray = $exjs ? preg_split("/ *, */", $exjs) : array();
	foreach($exjsarray as $index=>$value) {
		if (strstr($asset, $value)) {
			return true;
		}
	}
	return false;
}

function getCompiledJs() {
	global $sourcedir, $assetgraph, $prefixesstr;
	
	$js = "";
	
	foreach($assetgraph as $asset=>$value) {
		$type = strtolower(array_pop(explode(".", $asset)));
		if ($type == "js" && !inExcludeList($asset)) {
			$lines = file($sourcedir . $asset);
			$content = implode("", $lines);
			if (!mb_detect_encoding($content, 'UTF-8', true)) {
				$content = iconv("ISO-8859-1", "UTF-8", $content);
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