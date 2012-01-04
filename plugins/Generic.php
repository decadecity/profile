<?php
class Generic extends Plugin {
	
	public $profile;

	public function query($plugin, $features, $useragent) {
	
		try {
			$data = simplexml_load_file(dirname(__FILE__).$plugin['data']);
		} catch (Exception $e) {
			exit('Caught exception: '.$e->getMessage()."\n");
		}
		
		$profile = array();
		$features = $data->xpath("//feature");
		foreach ($features as $feature) {
			if ($feature['value']) {
				$profile[(string)$feature['id']] = (string)$feature['value'];
			}
		}
		
		$this->profile = $profile;
	}
}

?>