<?php
require 'Profile.php';
$device = new Profile("config.ini");

set_error_handler("custom_warning_handler", E_NOTICE);

function custom_warning_handler($errno, $errstr) {
// do something
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>TestPage</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1">
	<script type="text/javascript" src="profile.js.php"></script>
	<script type="text/javascript" src="resources/scripts/functions.js"></script>
	<link rel="stylesheet" type="text/css" href="resources/styles/profile.css" />
	<link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css' />
</head>
<body>
	<header>
		<h1>Profile</h1>
		<p>A pragmatic means <em>(and extremely early release)</em> of determining device features + contraints by the folks <a href="http://yiibu.com">@yiibu</a>.</p>
		<p>Please expect bugs. Documentation will follow.</p>
	<header>

	<h3>Title: <?php echo $device->features['title']; ?></h3>
	<p><strong>id:</strong> <?php echo $device->features['id']; ?>, <strong>version:</strong> <?php echo $device->features['version']; ?></p>
    <dl>
    	<dt class="header"><span class='feature'>Feature</span><span class='id'>(id:</span><span class='type'>type)</span><span class='value cloud'>cloud</span><span class='value client'>client</span></dt>
    	<div id="features">
    	<?php foreach ($device->features as $feature) {
    		$name = $feature->name;
    		$id = (string)$feature['id'];
    		$type = $feature['type'];
    		if (isset($device->profile[$id])) {
    			$cloud = $device->profile[$id];
    		} else {
    			$cloud = "n/a";
    		}
    		$description = $feature->description;
			$test = $feature->test;
			$test==""?$test="n/a":false;
    		echo "<dt><span class='feature'>$name</span><span class='id'>($id:</span><span class='type'>$type)</span>";
    		echo "<span class='value cloud'>$cloud</span><span id='$id' class='value client'>n/a</span></dt>";
    		echo "<dd><p>$description</p><h4>Test</h4><code>$test</code></dd>";
    	}
    	?>
    	<dt><span class='feature'>User Agent</span><span class='id'>(ua:</span><span class='type'>string)</span><span class='value cloud'>&hellip;</span><span class='value client'>&hellip;</span></dt><dd><code><?php echo $device->useragent; ?></code></dd>
    	</div>
    </dl>
    <nav>
    	<a id="refresh" class="button" href="#" onclick="window.location.reload();">Refresh</a>
		<a id="delete" class="button" href="#" onclick="deleteProfile();">Delete</a>
	</nav>

    <footer>
    	<p>Profile is released under the <a rel="license" href="http://www.slimframework.com/license">MIT Public License</a>, and the <a href="http://github.com/yiibu/profile">source code</a> is available <a href="http://github.com/yiibu/profile">via Github</a>.</p>
    	<p><small>&copy; copyright <a href="http://yiibu.com">yiibu</a> 2012</small></p>
    </footer>
<script>
if (window.location.search !== '?sent') {
  setTimeout(function () {
    window.location = window.location.pathname + '?sent';
  }, 500);
}
</script>
</body>
</html>
