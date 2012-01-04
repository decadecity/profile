<?php

class KnowledgeBase extends Plugin {
	
	public $profile;

	public function query($plugin, $features, $useragent) {
		try {
			$json = file_get_contents(dirname(__FILE__).$plugin['data']);
			$data = json_decode($json);
		} catch (Exception $e) {
			exit('Caught exception: '.$e->getMessage()."\n");
		}
		$profile = array();
		$matches = array();
		foreach ($data->profiles as $device => $fragment) {
			if (preg_match($fragment->match, $useragent)){array_push($matches, $fragment);}
		}
		foreach ($matches as $device){
			$new = (array) $device->profile;
			if ($new) {
				$old = $profile;
				$profile = array_merge($old,$new);
			}
		}
		
		$this->profile = $profile;
	}
}

?>