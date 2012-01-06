<?php

/**
 * Profile - a pragmatic means of determining device features + contraints by the folks @yiibu.
 *
 * @author      Bryan Rieger <hello@yiibu.com>
 * @copyright   2012 Yiibu Limited
 * @link        http://yiibu.com/profile
 * @license     http://yiibu.com/profile/license
 * @version     0.1
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

interface DataService {
	function query($plugin, $features, $useragent);
}

class Plugin implements DataService {
	
	public $plugin;
	public $profile;
	public $features;
	public $useragent;

	public function __construct($plugin, $features, $useragent) {
		$this->query($plugin, $features, $useragent);
		return $this;
	}

	function query($plugin, $features, $useragent) {
		$this->profile = array($this->plugin['obj'] => "query() not implemented");
	}
}

class Profile {

	public $profile;
	
	public $config;
	public $plugins;
	public $features;
	public $useragent;
	
	/*	
		TODO:
		------------------------------------------
		[ ] add comments + readme
		[x] add versioning (integrate current profile with new profile on change)
		[ ] ensure the Kb plugin only pulls requested features from the Kb data
		[x] add default profile values (pulls property values from profile.xml file)
		[ ] add WURFL plugin
		[x] add DejaVu plugin (uses most relevant profile found in the log)
		[x] figure out sort order (first is lowest priority, last is highest)
		[ ] document everything
		[ ] create a nice little initial test page
		[ ] create a few other variants of 'profile.xml' for folks to use…
		
	*/
	
	public function __construct($config) {
		// load config from supplied file name in $config rather than hard coded.
		try {
			$this->config = parse_ini_file("config.ini", true);
		} catch (Exception $e) {
			exit('Caught exception: '.$e->getMessage()."\n");
		}
		// load features dataset
		try {
			if (file_exists(dirname(__FILE__).$this->config['features'])) {
				$this->features = simplexml_load_file(dirname(__FILE__).$this->config['features']);
			} else {
				exit('Failed to open: '.$this->config['features']);
			}
		} catch (Exception $e) {
			exit('Caught exception: '.$e->getMessage()."\n");
		}
		
		$this->profile = array();
		$this->useragent = $_SERVER['HTTP_USER_AGENT'];
		if (empty($_COOKIE['profile'])) {
			$this->update();
		} else {
			$this->get();
		}
		$this->set();
		
	}
	public function get() {
		$ua = $_SERVER['HTTP_USER_AGENT'];
		
		$raw = urldecode(stripslashes($_COOKIE['profile']));
		$this->config['log']?$this->log($raw, $ua):false;
		
		$profile = json_decode($raw, true);
		
		foreach ($profile as $feature => $value) {
			$this->profile[$feature] = $value;
		}
		
		// check to see if the features profile version or id has changed
		$features[0] = $this->features['id'];
		$features[1] = $this->features['version'];
		$id = explode("-", $this->profile['id']);
		if ($id[0] != $features[0] or $id[1] != $features[1]) {
			$this->update();
		}
		
		
	}
	public function set() {
		try {
			setcookie('profile', urlencode(json_encode($this->profile)), time() + 3600 * 24 *30, '/');
		} catch (Exception $e) {
			exit('Caught exception: '.$e->getMessage()."\n");
		}
	}
	public function log($device_profile, $ua) {
		try {
			if (file_exists(dirname(__FILE__).$this->config['log'])) {
				$log = simplexml_load_file(dirname(__FILE__).$this->config['log']);
			} else {
				$log = simplexml_load_string("<log/>");
			}
		} catch (Exception $e) {
			exit('Caught exception: '.$e->getMessage()."\n");
		}
		$entry = $log->xpath("device[@ua='$ua']");
		$device_found = false;
		foreach ($entry as $device) {
			if ($device['ua'] == $ua) {
				$device_found = true;
				foreach ($device->profile as $profile) {
					$profile_found = false;
					if ((string)$profile == $device_profile) {
						$profile['count'] += 1;
						$profile_found = true;
						break;
					}
				}
				if (!$profile_found) {
					$new = $device->addChild("profile", $device_profile);
					$new->addAttribute('count', '1');
				}
			} 
		}
		if (!$device_found) {
				$device = new SimpleXMLElement("<device/>");
				$device->addAttribute('ua', $ua);
				$profile = $device->addChild("profile", $device_profile);
				$profile->addAttribute('count', '1');
				// munge the $device back into the $log…
				$dom_log = dom_import_simplexml($log);
				$dom_device  = dom_import_simplexml($device);
				$dom_device  = $dom_log->ownerDocument->importNode($dom_device, TRUE);
				$dom_log->appendChild($dom_device);
		}
		$log->asXML(dirname(__FILE__).$this->config['log']);
	}
	public function normalise() {
		foreach($this->profile as $feature => $value) {
			if ($value === true) {
				$this->profile[$feature] = 1;
			} 
		}
	}
	public function update() {
		// load data plugins
		$ua = $_SERVER['HTTP_USER_AGENT'];
		foreach ($this->config as $plugin => $api) {
			if (is_array($api)) {
				$interface = dirname(__FILE__).$this->config['plugins'].$plugin;
				require $interface.".php";
				$features = array();
				foreach ($this->features as $feature) {
					$result = $feature->xpath("data/plugin[@id='$plugin']");
					($result)?$features[(string)$feature['id']] = (string)$result[0]['property']:false;
				}
				$obj = new $plugin($api, $features, $ua);
				$this->plugins[$plugin] = $obj->profile;
			}
		}
		// merge data into profile
		$this->profile = call_user_func_array('array_merge', $this->plugins);
		$this->profile['id'] = $this->features['id']."-".$this->features['version'];
		$this->normalise();
	}
}
?>