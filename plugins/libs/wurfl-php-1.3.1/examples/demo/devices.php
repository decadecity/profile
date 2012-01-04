<?php

include_once './inc/header-config.inc';

function printDeviceDetails($device) {

	if (isset($device)) {
		print"<ul>";
		print"<li>ID: $device->id</li>";
		print"<li>Brand Name: " . $device->getCapability("brand_name") . "</li>";
		print"<li>Model Name: " . $device->getCapability('model_name') . "</li>";
		print"<li>Xhtml Preferred Markup:" .  $device->getCapability('preferred_markup') . "</li>";
		print"<li>Resolution Width:" .  $device->getCapability('resolution_width') . "</li>";
		print"<li>Resolution Height:" .  $device->getCapability('resolution_height') . "</li>";
		print"<li>MP3:" .  $device->getCapability('mp3') . "</li>";
		print"</ul>";
	}

}


$device = null;

if ($_GET['userAgent']) {
	$userAgent = $_GET['userAgent'];
	// $device is a WURFL_CustomDevice object
	$device = $wurflManager->getDeviceForUserAgent($userAgent);
}

?>

<div id="content">
<p><b>Query WURFL by providing a user agent:</b></p>
<form method="get" action="devices.php">
<div>User Agent: <input type="text" name="userAgent" size="255" value="" />
<input type="submit" name="Submit" value="Submit"/></div>
</form>

<?php printDeviceDetails($device); ?>

</div>
<?php
include_once 'inc/footer.inc';
?>