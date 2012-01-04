<?php

define("WURFL_DIR", 	dirname(__FILE__) . '/../../WURFL/');
define("RESOURCES_DIR", dirname(__FILE__) . '/../resources/');

require_once WURFL_DIR . 'Application.php';

$wurflConfigFile = RESOURCES_DIR . 'wurfl-config.xml';
$wurflConfig = new WURFL_Configuration_XmlConfig($wurflConfigFile);

$wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);

$wurflManager = $wurflManagerFactory->create();	
$wurflInfo = $wurflManager->getWURFLInfo();

define("XHTML_ADVANCED", "xhtml_advanced.php");
define("XHTML_SIMPLE", "xhtml_simple.php");
define("WML", "wml.php");

$device = $wurflManager->getDeviceForHttpRequest($_SERVER);

$xhtml_lvl = $device->getCapability('xhtml_support_level');
$contentType = $device->getCapability('xhtmlmp_preferred_mime_type');

$page = getPageFromMarkup($xhtml_lvl);
redirectToPage($page, $contentType);

function getPageFromMarkup($xhtml_lvl) {
	$page = WML;
	switch ($xhtml_lvl) {
		/* xhtml_support_level possible values:
		 * -1: No XHTML Support
		 *  0: Poor XHTML Support
		 *  1: Basic XHTML with Basic CSS Support
		 *  2: Same as Level 1
		 *  3: XHTML Support with Excellent CSS Support
		 *  4: Level 3 + AJAX Support
		 */
		case 1:
		case 2:
			$page = XHTML_SIMPLE;
			break;
		case 3:
		case 4:
			$page = XHTML_ADVANCED;
			break;
		default:
			$page = WML;
			break;
	}
	return $page;
}

function redirectToPage($page, $contentType) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

	header('Content-Type: ' . $contentType . '\'');
	header("Location: http://$host$uri/$page");
	exit;
}