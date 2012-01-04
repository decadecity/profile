<?php 
require 'Profile.php';
$device = new Profile("config.ini");
$profile = $device->profile;
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>TestPage</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, minimum-scale=1">
	<script type="text/javascript" src="profile.js"></script>
	<style type="text/css">
		table {
			width: 100%;
			border: 0;
		}
		thead td, tfoot td {
			font-weight: bold;
			background: #ddd;
			border-bottom: 2px solid #ccc;
		}
		td { width: 33%; padding: .25em }
		tr { background: #eee;}
		dt {
			float:left;
			padding: .5em .25em;
			color:#999;
			
		}
		dd {
			padding: .5em;
			font-weight: bold;
		}
	</style>
</head>
<body>
	<h1>Device Profile</h1>
    <p><a href="http://github.com/bryanrieger/profile">Source available on Github</a></p>
	<?php
		echo "<h2>UA String</h2>";
		echo "<dl>";
		echo "<dt>ua string</dt><dd>".$_SERVER['HTTP_USER_AGENT']."</dd>";
		echo "</dl>";
	?>
	<h2>Features</h2>
    <table>
    <thead>
    	<tr><td>Feature</td><td>Server</td><td>Client</td></tr>
    </thead>
    <tbody id="features">
	<?php
    	foreach ($profile as $name => $value) {
			echo "<tr id=".$name."><td>".$name."</td><td>".$value."</td></tr>";
		}
	?>
	</tbody>
    <tfoot>
    	<tr>
        	<td><a href="#" onclick="window.location.reload();">Reload</a></td>
        	<td></td>
        	<td><a href="#" onclick="clearProfile();">Clear profile</a></td>
        </tr>
    </tfoot>
    </table>
	<script type="text/javascript">
    	for (var feature in device.profile) {
    		var f_id = document.getElementById(feature);
    		if (f_id) {
    			var td = document.createElement('td');
    			td.innerHTML =  device.profile[feature];
    			f_id.appendChild(td);
    		} else {
    			var tr = document.createElement('tr');
    			var td1 = document.createElement('td')
    			var td2 = document.createElement('td')
    			var td3 = document.createElement('td')
    			td1.innerHTML = feature;
    			tr.appendChild(td1);
    			tr.appendChild(td2);
    			td3.innerHTML = device.profile[feature];
    			tr.appendChild(td3);
    			var tbl = document.getElementById('features').appendChild(tr);
    		}
		}
		function clearProfile() {
			window.device.clear('profile')
			var tbl = document.getElementById('features');
			tbl.parentNode.removeChild(tbl);
		} 
		
    </script>
</body>
</html>