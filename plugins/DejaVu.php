<?php

class DejaVu extends Plugin {
	
	public $profile;

	public function query($plugin, $features, $useragent) {
		try {
			if (file_exists(dirname(__FILE__).$plugin['data'])) {
				$data = simplexml_load_file(dirname(__FILE__).$plugin['data']);
			} else {
				exit('Failed to open: '.$plugin['data']);
			}
		} catch (Exception $e) {
			exit('Caught exception: '.$e->getMessage()."\n");
		}
		
		$entry = $data->xpath("device[@ua='$useragent']");
		$device_profile = null;
		foreach ($entry as $device) {
			if ($device['ua'] == $useragent) {
				$profile_trust = 0;
				foreach ($device->profile as $profile) {
					if ((integer)$profile['count'] > $profile_trust) {
						$device_profile = (string)$profile; 
						$profile_trust = $profile['count'];
					}
				}
			}
		}
		$this->profile =  json_decode($device_profile, true);;
	}
}

?>