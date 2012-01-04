<?php

define("WURFL_DIR", dirname(__FILE__) . '/../../../WURFL/');
define("RESOURCES_DIR", dirname(__FILE__) . "/../../resources/");

require_once WURFL_DIR . 'Application.php';

// Uncomment the follwoing lines to use the xml configuration file
//$wurflConfigFile = RESOURCES_DIR . 'wurfl-config.xml';
//$wurflConfig = new WURFL_Configuration_XmlConfig($wurflConfigFile);

$persistenceDir = RESOURCES_DIR . "storage/persistence";
$cacheDir = RESOURCES_DIR . "storage/cache";
$wurflConfig = new WURFL_Configuration_InMemoryConfig ();
$wurflConfig
        ->wurflFile(RESOURCES_DIR . "wurfl-2.0.23.zip")
        ->wurflPatch(RESOURCES_DIR . "web_browsers_patch.xml")
        ->persistence("apc",array("namespace"=>"wurflpersist"))
        ->cache("apc", array(
                            "namespace" => "wurfl",
                            WURFL_Configuration_Config::EXPIRATION => 36000));


$wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);

$wurflManager = $wurflManagerFactory->create();
$wurflInfo = $wurflManager->getWURFLInfo();

?>

<h3>WURFL XML INFO</h3>
<ul>
    <li><h4>VERSION: <?php echo $wurflInfo->version; ?> </h4></li>
</ul>
