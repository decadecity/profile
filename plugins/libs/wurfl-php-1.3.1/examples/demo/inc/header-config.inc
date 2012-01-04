<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);

define("WURFL_DIR", dirname(__FILE__) . '/../../../WURFL/');
define("RESOURCES_DIR", dirname(__FILE__) . "/../../resources/");

require_once WURFL_DIR . 'Application.php';

$wurflConfigFile = RESOURCES_DIR . 'wurfl-config.xml';
// Create WURFL Configuration from an XML config file
$wurflConfig = new WURFL_Configuration_XmlConfig($wurflConfigFile);
// Create a WURFL Manager Factory from the WURFL Configuration
$wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
// Create a WURFL Manager ($wurflManager is a WURFL_WURFLManager object)
$wurflManager = $wurflManagerFactory->create();
// Get information on the loaded WURFL ($wurflInfo is a WURFL_Xml_Info object)
$wurflInfo = $wurflManager->getWURFLInfo();
?>

<h3>WURFL XML INFO</h3>
<ul>
    <li><h4>VERSION: <?php echo $wurflInfo->version; ?> </h4></li>
</ul>
