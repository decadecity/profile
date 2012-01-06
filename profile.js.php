<?php
	try {
		$config = parse_ini_file("config.ini", true);
		if (file_exists(dirname(__FILE__).$config['features'])) {
			$profile_time = filemtime(dirname(__FILE__).$config['features']);
		} else {
	   		exit('Failed to open'.$config['profile']);
		}
		
		if (file_exists(dirname(__FILE__).$config['javascript_src'])) {
			$js_time = filemtime(dirname(__FILE__).$config['javascript_src']);
		} else {
	   		exit('Failed to open'.$config['javascript_src']);
		}
		
		if (file_exists(dirname(__FILE__).$config['javascript_cache'])) {
			$cache_time = filemtime(dirname(__FILE__).$config['javascript_cache']);
		}
	} catch (Exception $e) {
		echo 'Caught exception: ', $e->getMessage(), "\n";
	}
	if ($cache_time < $profile_time || $cache_time < $js_time) {
		$js = file_get_contents(dirname(__FILE__).$config['javascript_src']);
		$profile = simplexml_load_file(dirname(__FILE__).$config['features']);
		
		// include all of the supplied tests
		$script = "";
		foreach ($profile as $feature) {
			if ($feature->test) { $script .= $feature['id'].":function(){".$feature->test."},"; }
		}
		$script = rtrim($script, ",");
		
		$javascript = str_replace("[FEATURE_DETECTION]", $script, $js);
		// remove all /* comments */
		$javascript = preg_replace("/\\/\\*.+?\\*\\//uis", '', $javascript);
		  // remove all extra spaces, tabs and newlines
 		$javascript = preg_replace("/(\s\s+|\t|\n)/",'',$javascript);
 		file_put_contents(dirname(__FILE__).$config['javascript_cache'], $javascript);
	} else {
		$javascript = file_get_contents(dirname(__FILE__).$config['javascript_cache']);
	}
	header("content-type: application/x-javascript");
	flush();
	echo $javascript;
?>