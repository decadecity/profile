<?php

class DeviceAtlas extends Plugin {
	public $profile;
	
	public function query($plugin, $features, $useragent) {
		try {
			// make sure we have an api
			if (file_exists(dirname(__FILE__).$plugin['api'])) {
				include dirname(__FILE__).$plugin['api'];
			} else {
   				exit('Failed to open: '.$plugin['api']);
			}
			// make sure we have some data…
			if (file_exists(dirname(__FILE__).$plugin['data'])) {
				// all is good
			} else {
   				exit('Failed to open: '.$plugin['data']);
			}
		} catch (Exception $e) {
    		exit('Caught exception: '.$e->getMessage()."\n");
		}
		
		$tree = null;
		$s = microtime(true);
		
		$memcache_enabled = extension_loaded("memcache");
		$no_cache = array_key_exists("nocache", $_GET);
		if ($memcache_enabled && !$no_cache) {
		  $memcache = new Memcache;
		  $memcache->connect('localhost', 11211);
		  $tree = $memcache->get('tree');
		}
		
		if (!is_array($tree)) {
		  $tree = Mobi_Mtld_DA_Api::getTreeFromFile(dirname(__FILE__).$plugin['data']);
		  if ($memcache_enabled && !$no_cache) {
		    $memcache->set('tree', $tree, false, 10);
		  }
		}
		
		if ($memcache_enabled && !$no_cache) {
		  $memcache->close();
		}
		$profile = array();
		$deviceatlas = Mobi_Mtld_DA_Api::getProperties($tree, $useragent);

		foreach ($features as $property => $value) {
			if (isset($deviceatlas[$value])) {
				$profile[$property] = $deviceatlas[$value];
			} else {
				unset($features[$property]);
			}
		}
		$this->profile = $profile;
	}
	
}

?>