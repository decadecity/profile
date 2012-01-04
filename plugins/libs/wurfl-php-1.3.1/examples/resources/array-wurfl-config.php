<?php

/*
 * Example of WURFL PHP API Array-based Configuration
 */

$configuration = array(
	// WURFL File Configuration
	'wurfl' => array(
		'main-file' => 'wurfl-regression.zip',
		'patches' => array("web_browsers_patch.xml"),
	),
	// Persistence (Long-Term Storage) Configuration
	'persistence' => array(
		'provider' => 'memcache',
		'dir' => 'cache',
	),
	// Cache (Short-Term Storage) Configuration
	'cache' => array(
		'provider' => 'null',
	),
);