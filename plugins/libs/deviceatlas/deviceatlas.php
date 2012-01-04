<?php

/**
 * Welcome to DeviceAtlas Cloud! All you need to get going is to set your DeviceAtlas
 * licence key below and include this file in your web page.
 *
 * Device data can then be retrieved by calling
 * $data = DeviceAtlasCloudClient::getDeviceData();
 *
 * The returned data will contain the following:
 * $data['properties']  - contains the device properties
 * $data['error']  - contains any errors that occurred when fetching the properties
 * $data['source'] -  states where the data came from, one of: 'cookie', 'cache', 'cloud' or 'none'.
 * $data['useragent'] - contains the useragent that was used to query for data
 *
 *  @copyright Copyright © 2011 dotMobi. All rights reserved.
 */
class DeviceAtlasCloudClient {


	/*********** BASIC SETUP ***********/
	// all you need to get going!
	const LICENCE_KEY = '7682904e46c28509bf85f3d4c53038d3';


	/*********** ADVANCED SETUP ***********/
	// edit these if you want to tweak behaviour

	const USE_COOKIE_CACHE = true;
	const USE_FILE_CACHE = true;
	const CACHE_ITEM_EXPIRY_SEC = 2592000; //  2592000  = 30 days in seconds
	const CACHE_NAME = 'deviceatlas_cache';
	const COOKIE_NAME = 'Mobi_Mtld_DA_Properties';
	const CLOUD_HOST = 'api.deviceatlascloud.com';
	const CLOUD_PORT = '80';
	const CLOUD_PATH = '/v1/detect/properties?licencekey=%s&useragent=%s';
	const TEST_USERAGENT = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; es-es) AppleWebKit/533.17.9 (KHTML, like Gecko) Mobile/8C148';

	const USE_SYSTEM_TEMP_DIR = true; // leave as true to put cache in systems default temp directory
	const CUSTOM_CACHE_DIR = '/path/to/your/cache/'; // this is only used if USE_SYSTEM_TEMP_DIR is false.
	
	/*********** END OF SETUP ***********/


	/////////////////////////////////////////////////////////////////////
	// no need to edit below here!

	/** CONSTANTS **/
	const KEY_USERAGENT = 'useragent';
	const KEY_SOURCE = 'source';
	const KEY_ERROR = 'error';
	const KEY_PROPERTIES = 'properties';
	const SOURCE_COOKIE = 'cookie';
	const SOURCE_FILE_CACHE = 'cache';
	const SOURCE_CLOUD = 'cloud';
	const SOURCE_NONE = 'none';
	const DA_HEADER_PREFIX = 'X-DA-';
	const API_VERSION = 'php/1.0';
	
	// a list of headers from the end user to pass to DeviceAtlas Cloud. These
	// help with detection, especially if a third party browser or a proxy changes
	// the original user-agent.
	private static $USER_HEADERS = array('HTTP_X_PROFILE',
											'HTTP_X_WAP_PROFILE',
											'HTTP_X_DEVICE_USER_AGENT',
											'HTTP_X_ORIGINAL_USER_AGENT',
											'HTTP_X_SKYFIRE_PHONE',
											'HTTP_X_BOLT_PHONE_UA',
											'HTTP_ACCEPT',
											'HTTP_ACCEPT_LANGUAGE');
	/** END CONSTANTS **/


	/**
	 * Gets device data from DeviceAtlas Cloud. Once data has been returned from
	 * DeviceAtlas Cloud it can be cached locally to speed up subsequent requests.
	 * 
	 */
	public static function getDeviceData($test_mode=false) {
		$results = array();
		$results[self::KEY_SOURCE] = self::SOURCE_NONE;

		if($test_mode) {
			$user_agent = self::TEST_USERAGENT;
		} else {
			$user_agent = $_SERVER['HTTP_USER_AGENT']; // get the clients useragent
		}

		$results[self::KEY_USERAGENT] = $user_agent;

		try {
			$device_data = null;

			// check cookie for cached data
			if(self::USE_COOKIE_CACHE) {
				$device_data = self::cookieGet();
				$results[self::KEY_SOURCE] = self::SOURCE_COOKIE;
			}

			// check file cache for cached data
			if(self::USE_FILE_CACHE && empty($device_data)) {
				$device_data = self::cacheGet($user_agent);
				$results[self::KEY_SOURCE] = self::SOURCE_FILE_CACHE;
			}

			// finally fall back to fetching from cloud
			if(empty($device_data)) {
				$device_data = self::cloudGet($user_agent);
				$device_data = self::decodeData($device_data); // we need to decode the json
				$results[self::KEY_SOURCE] = self::SOURCE_CLOUD;
			}

			// now that we have the device data we need to decode the json
			$results = array_merge($results, $device_data);

			// set the caches for future queries
			// we only want to cache the actual properties and not any extra info
			self::setCaches($user_agent, array(self::KEY_PROPERTIES=>$device_data[self::KEY_PROPERTIES]), $results[self::KEY_SOURCE]);

		// handle errors
		} catch (Exception $e) {
			$error_msg = $e->getMessage();
			$results[self::KEY_ERROR] = $error_msg;
			// also log to error log
			error_log($error_msg);
		}
		
		return $results;
	}



	/**
	 * Try and get data from the DeviceAtlas Cloud service.
	 */
	private static function cloudGet($user_agent) {
		$header = self::prepareRequestHeader($user_agent);

		// get a handle to the socket
		$fp = fsockopen(self::CLOUD_HOST, self::CLOUD_PORT, $errno, $errstr, 10);
		if (!$fp) {
			throw new Exception('Error fetching DeviceAtlas data from Cloud '.$errstr.' ('.$errno.')');
		} else {
			fwrite($fp, $header);

			$results = '';
			while (!feof($fp)) {
				$results .= fgets($fp, 128);
			}
			fclose($fp);

			// read headers and body...
			$parts = @explode("\r\n\r\n", $results, 2);
			if(count($parts) == 2) {
				$headers = @explode("\r\n", $parts[0]);
				$status = @explode(" ", $parts[0]);

				$status = $status[1];
				$body = trim($parts[1]);

				if((int)($status/100) != 2) {
					throw new Exception('Error fetching DeviceAtlas data from Cloud. '.$status.' '.$body);
				} else {
					$device_data = $body;
				}
			} else {
				throw new Exception('Error fetching DeviceAtlas data from Cloud. Cant parse results. '.$results);
			}
		}

		return $device_data;
	}


	
	/**
	 * Prepare the request header. End user headers are prefixed with X-DA-
	 *
	 * @param string $user_agent
	 * @return string
	 */
	private static function prepareRequestHeader($user_agent) {
		$path = sprintf(self::CLOUD_PATH, self::LICENCE_KEY, urlencode($user_agent));

		// prepare headers
		$headers_str = "GET ".$path." HTTP/1.0\r\n";
		$headers_str .= "Host: ".self::CLOUD_HOST."\r\n";
		$headers_str .= "Accept: application/json\r\n";
		$headers_str .= "User-Agent: php\r\n"; // fsockopen sometimes needs a UA
		$headers_str .= self::DA_HEADER_PREFIX."Version: ".self::API_VERSION."\r\n";

		// get all the end user headers and wrap them up in X-DA- headers to send
		// to the server
		if(!empty($_SERVER)) {
			foreach(self::$USER_HEADERS as $header) {
				if(isset($_SERVER[$header])) {
					$headers_str.= self::convertHeader($header).": ".$_SERVER[$header]."\r\n";
				}
			}

			// look for opera headers
			foreach($_SERVER as $header => $val) {
				if(stristr($header, 'opera')) {
					$headers_str.= self::convertHeader($header).": ".$val."\r\n";
				}
			}
		}

		$headers_str .= "Connection: Close\r\n\r\n";
		return $headers_str;
	}




	/**
	 * Utility function to convert a PHP header into the standard format but with
	 * the X-DA- prefix
	 * e.g.	HTTP_ACCEPT_LANGUAGE  -->  X-DA-Accept-Language
	 */
	private function convertHeader($header) {
		$header = strtolower($header);
		// replace http at start
		if(strpos($header, 'http_') === 0) {
			$header = substr($header, 5);
		}
		// replace _ with a space so we can use ucwords and then replace
		// space with a -
		$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', $header)));
		return self::DA_HEADER_PREFIX.$header;
	}




	/**
	 * Set the cookie and file caches with the device data
	 */
	private static function setCaches($user_agent, $device_data, $source) {
		$device_data = serialize($device_data); // serialize the array
		
		// always set cookie cache if we can
		if(self::USE_COOKIE_CACHE && $source!=self::SOURCE_COOKIE) {
			setcookie(self::COOKIE_NAME, $device_data);
		}

		// set cache for future queries only if the source was from the cloud
		if(self::USE_FILE_CACHE && $source==self::SOURCE_CLOUD) {
			self::cachePut($user_agent, $device_data);
		}
	}



	/**
	 * Try and get device data stored in the user's cookie
	 */
	private static function cookieGet() {
		$device_data = null;

		if (isset($_COOKIE[self::COOKIE_NAME])) {
			$device_data = stripslashes($_COOKIE[self::COOKIE_NAME]);
		}

		return unserialize($device_data);
	}



	/**
	 * Try and find the devices data from the file cache.
	 */
	private static function cacheGet($user_agent) {
		$device_data = null;
		
		$path = self::getCachePath(md5($user_agent));

		// check file modification time
		if(file_exists($path)) {
			$mtime = @filemtime($path);
			if($mtime + self::CACHE_ITEM_EXPIRY_SEC > time()) {
				$device_data = @file_get_contents($path);
			}
		}
		
		return unserialize($device_data);
	}

	
	/**
	 * Put the device data in the file cache
	 */
	private static function cachePut($user_agent, $device_data) {
		$res = true;

		$path = self::getCachePath(md5($user_agent));
		@mkdir(dirname($path), 0755, true);
		if(@file_put_contents($path, $device_data, LOCK_EX) === false) {
			throw new Exception('Unable to write cache file at '.$path);
		}

		return $res;
	}



	/**
	 * Creates a cache path for this item by taking the md5 hash
	 * and using the first 4 characters to create a directory structure.
	 * This is done to prevent too many files existing in any one directory
	 * as this can lead to slowdowns.
	 */
	private static function getCachePath($md5) {
		$first_dir = substr($md5, 0, 2);
		$second_dir = substr($md5, 2, 2);
		$file_name = substr($md5, 4, strlen($md5));

		$base_path = '';
		if(self::USE_SYSTEM_TEMP_DIR) {
			$base_path = sys_get_temp_dir();
		} else {
			$base_path = self::CUSTOM_CACHE_DIR;
		}

		$base_path .= DIRECTORY_SEPARATOR.self::CACHE_NAME.DIRECTORY_SEPARATOR;
		return $base_path.$first_dir.DIRECTORY_SEPARATOR.$second_dir.DIRECTORY_SEPARATOR.$file_name;
	}



	/**
	 * Decodes the JSON data and extracts the data
	 */
	private static function decodeData($device_data) {
		$props = null;
		
		if(!empty($device_data)) {
			$props = (array)json_decode($device_data, TRUE);

			// make sure that at the very least the properties are present
			if(!isset($props[self::KEY_PROPERTIES])) {
				throw new Exception('Cannot get device properties from "'.$device_data.'"');
			}
		}

		return $props;
	}

}


?>
