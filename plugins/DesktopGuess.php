<?php

/**
 * If you are using DeviceAtlas then you are strongly recommended to disable
 * this plugin.
 *
 * This does some string matching to try and guess if the user agent is a
 * desktop browser.  This takes a "mobile first" approach to the guess work as
 * there are a smaller number of desktop browsers and it should be easier to
 * cover them than assume a desktop and try and cope with the huge range of
 * mobile browsers out there.
 *
 * The usual caveats about user agent sniffing apply here - you should always
 * try and work out what data you actually want (such as screen size) and try
 * and get that directly rather than rely on this.  For more on this please read
 * http://www.quirksmode.org/blog/archives/2010/09/state_of_mobile_2.html#link5
 *
 * When this file is run from the command line or is called directly (i.e. not
 * included) then a set of tests are run to check the user agent sniffing.  If
 * you find a user agent that is not being correctly detected then add it to the
 * user agent list and modify the detection until all tests pass.
 *
 * To keep this maintainable the browsers should be listed in alphabetical
 * order for both detection and testing.
 *
 * Before editing the user agent sniffing you should read the following:
 * http://webaim.org/blog/user-agent-string-history/
 */

if (!count(debug_backtrace()) && !class_exists('Plugin')) {
	// We're not being included and Plugin isn't defined so define it so the
	// inheritance chain will work.
	class Plugin {};
}

class DesktopGuess extends Plugin {

	public $profile;

	public function query($plugin, $features, $useragent) {
		$this->profile = array();
		if (in_array('isDesktop', $features)) {
			$desktop = false; // Mobile first.
			$ua = strtolower($useragent);

			/* This is the user agent sniffing, it's broken out into the major
			 * name of the browser (in alphabetical order) and then any further
			 * detection is done within the major name's if block.
			 */
			if (strpos($ua, 'camino') !== false) {
				$desktop = true;
			}
			if (strpos($ua, 'chrome') !== false) {
				// So far there are no mobile Chrome UAs - that will change.
				$desktop = true;
			}
			if (strpos($ua, 'firefox') !== false) {
				if (
					strpos($ua, 'mobile') === false &&
					strpos($ua, 'fennec') === false &&
					strpos($ua, 'maemo') === false
				) {
					$desktop = true;
				}
			}
			if (strpos($ua, 'konqueror') !== false) {
				$desktop = true;
			}
			if (strpos($ua, 'msie') !== false) {
				if (strpos($ua, 'mobile') === false) {
					$desktop = true;
				}
			}
			if (strpos($ua, 'netscape') !== false) {
				$desktop = true;
			}
			if (strpos($ua, 'opera') !== false) {
			    if (
					strpos($ua, 'mobi') === false  &&
					strpos($ua, 'mini') === false
				) {
					$desktop = true;
				}
			}
			// Safari's the most complicated - it pops up everywhere.
			if (strpos($ua, 'safari') !== false) {
			    if (strpos($ua, 'windows') !== false) {
					$desktop = true;
				}
				if (strpos($ua, 'mac os') !== false) {
					if (
						strpos($ua, 'iphone') === false &&
						strpos($ua, 'ipad') === false
					) {
						$desktop = true;
					}
				}
			}

			$profile = array('desktop' => (int)$desktop);
			$this->profile = $profile;
		}
	}
}

// Unit test runner for when we're not an include.
if (!count(debug_backtrace())) {
	$cls = new DesktopGuess();

	/* This is a list of user agents that are used for testing the detection,
	 * they are mostly taken from http://www.useragentstring.com/
	 *
	 * It's not an exhaustive list but tests most of the major browsers.
	 *
	 * The list is arranged in alphabetical order of browser name.
	 */
	$user_agent_list = array(
		// Android
		'Mozilla/5.0 (Linux; U; Android 2.3.5; en-us; HTC Vision Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => 0,
		// Blackberry
		'Mozilla/5.0 (BlackBerry; U; BlackBerry 9850; en-US) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.0.0.115 Mobile Safari/534.11+' => 0,
		'BlackBerry9700/5.0.0.862 Profile/MIDP-2.1 Configuration/CLDC-1.1 VendorID/331' => 0,
		// Chrome
		'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.75 Safari/535.7' => 1,
		// Firefox
		'Mozilla/5.0 (X11; Linux i686 on x86_64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1' => 1,
		'Mozilla/5.0 (Android; Linux armv7l; rv:9.0) Gecko/20111216 Firefox/9.0 Fennec/9.0' => 0,
		'Mozilla/5.0 (X11; U; Linux armv7l; ru-RU; rv:1.9.2.3pre) Gecko/20100723 Firefox/3.5 Maemo Browser 1.7.4.8 RX-51 N900' => 0,
		'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en; rv:1.9.2.14pre) Gecko/20101212 Camino/2.1a1pre (like Firefox/3.6.14pre)' => 1,
		'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en; rv:1.9.0.8pre) Gecko/2009022800 Camino/2.0b3pre' => 1,
		// IE
		'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)' => 1,
		'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)' => 1,
		'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))' => 1,
		'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)' => 1,
		'Mozilla/4.0(compatible; MSIE 7.0b; Windows NT 6.0)' => 1,
		'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; el-GR)' => 1,
		'Mozilla/5.0 (MSIE 7.0; Macintosh; U; SunOS; X11; gu; SV1; InfoPath.2; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)' => 1,
		'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1)' => 1,
		'Mozilla/4.0 (compatible; U; MSIE 6.0; Windows NT 5.1) (Compatible; ; ; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)' => 1,
		'Mozilla/4.0 (compatible; MSIE 5.5; Windows NT 6.1; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)' => 1,
		'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0)' => 0,
		'HTC_Touch_3G Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 7.11)' => 0,
		'Mozilla/4.0 (compatible; MSIE 7.0; Windows Phone OS 7.0; Trident/3.1; IEMobile/7.0; Nokia;N70)' => 0,
		// Konqueror
		'Mozilla/5.0 (compatible; Konqueror/4.5; FreeBSD) KHTML/4.5.4 (like Gecko)' => 1,
		// Netfront
		'SAMSUNG-C5212/C5212XDIK1 NetFront/3.4 Profile/MIDP-2.0 Configuration/CLDC-1.1' => 0,
		// Netscape
		'Mozilla/5.0 (Windows; U; Win 9x 4.90; SG; rv:1.9.2.4) Gecko/20101104 Netscape/9.1.0285' => 1,
		// Opera
		'Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00' => 1,
		'Mozilla/5.0 (Windows NT 5.1; U; Firefox/4.5; en; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 Opera 10.53' => 1,
		'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; ru) Opera 8.50' => 1,
		'Opera/9.80 (J2ME/MIDP; Opera Mini/9.80 (S60; SymbOS; Opera Mobi/23.334; U; id) Presto/2.5.25 Version/10.54' => 0,
		'Opera/9.80 (Android 2.3.3; Linux; Opera Mobi/ADR-1111101157; U; es-ES) Presto/2.9.201 Version/11.50' => 0,
		'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 9.51, or Mozilla/5.0 (Windows NT 6.0; U; en; rv:1.8.1) Gecko/20061208 Firefox/2.0.0 Opera 9.51' => 1,
		// Safari
		'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1' => 1,
		'Mozilla/5.0 (Windows; U; Windows NT 6.1; ko-KR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27' => 1,
		'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; nb-no) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148a Safari/6533.18.5' => 0,
		'Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10gin_lib.cc' => 0,
		// Symbian
		'Mozilla/5.0 (SymbianOS/9.4; Series60/5.0 NokiaC6-00/20.0.042; Profile/MIDP-2.1 Configuration/CLDC-1.1; zh-hk) AppleWebKit/525 (KHTML, like Gecko) BrowserNG/7.2.6.9 3gpp-gba' => 0,
		'SamsungI8910/SymbianOS/9.1 Series60/3.0' => 0,
		'NokiaC5-00/061.005 (SymbianOS/9.3; U; Series60/3.2 Mozilla/5.0; Profile/MIDP-2.1 Configuration/CLDC-1.1) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 Safari/525 3gpp-gba' => 0,
	);

	// Now actually run the test for each UA in the list.
	$errors = array();
	foreach($user_agent_list as $ua => $result) {
		$cls->query(null, array('desktop' => 'isDesktop'), $ua);
		if ($cls->profile['desktop'] != $result) {
			$errors[] = "Test failed for user agent: $ua\nExpected result was: $result\nActual result was: {$cls->profile['desktop']}\n--\n";
		}
	}
	// Output of test result.
	if (php_sapi_name() != 'cli') {
		// We are not on the command line - assume a web page.
		header("Content-Type: text/plain");
	}
	if (count($errors)) {
		foreach ($errors as $error) {
			print($error);
		}
		print(count($user_agent_list) - count($errors) . " tests passed.\n");
		print(count($errors) . " tests failed.\n");
		exit(1);
	} else {
		print("All " . count($user_agent_list) . " tests passed.\n");
	}
}
