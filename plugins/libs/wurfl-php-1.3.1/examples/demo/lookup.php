<?php
// This example demonstrates the use of InMemoryConfig() and looking up user agents manually
define("WURFL_DIR", dirname(__FILE__) . '/../../WURFL/');
define("RESOURCES_DIR", dirname(__FILE__) . "/../resources/");

require_once realpath(WURFL_DIR . 'Application.php');
require_once realpath(WURFL_DIR . '/Configuration/InMemoryConfig.php');

$persistenceDir = realpath(RESOURCES_DIR . "storage/persistence");
$cacheDir = realpath(RESOURCES_DIR . "storage/cache");
$wurflConfig = new WURFL_Configuration_InMemoryConfig();
$wurflConfig
        ->wurflFile(realpath(RESOURCES_DIR . "wurfl.zip"))
        ->wurflPatch(realpath(RESOURCES_DIR . "web_browsers_patch.xml"))
        //->persistence("apc",array("namespace"=>"wurflpersist"))
        ->persistence("memory")
        //->cache("apc", array("namespace" => "wurfl", WURFL_Configuration_Config::EXPIRATION => 36000));
        ->cache("null");
$wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
$wurflManager = $wurflManagerFactory->create();
$wurflInfo = $wurflManager->getWURFLInfo();
$requestingDevice = $wurflManager->getDeviceForUserAgent('Mozilla/4.0 (compatible; MSIE 7.0; Windows Phone OS 7.0; Trident/3.1; IEMobile/7.0; HTC; 7 Trophy T8686)');
?>

<div id="content">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<label for="ua">User Agent: <input type="text" name="ua" id="ua" size="40" value="<?php echo isset($_POST['ua'])? $_POST['ua']: ''; ?>"/></label><br/>
</form>
Requesting Browser User Agent: <b> <?php echo htmlspecialchars($_SERVER["HTTP_USER_AGENT"]); ?> </b>


<ul>
<li>ID: <?php echo $requestingDevice->id; ?> </li>
<li>Brand Name: <?php echo $requestingDevice->getCapability("brand_name"); ?> </li>
<li>Model Name: <?php echo $requestingDevice->getCapability("model_name"); ?> </li>
<li>Xhtml Preferred Markup: <?php echo $requestingDevice->getCapability("preferred_markup"); ?> </li>
<li>Resolution Width: <?php echo $requestingDevice->getCapability("resolution_width"); ?> </li>
<li>Resolution Height: <?php echo $requestingDevice->getCapability("resolution_height"); ?> </li>
</ul>
</div>
<?php
include_once 'inc/footer.inc';
?>
