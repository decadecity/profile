<?php
/**
 * Command line script to merge two dejavu.xml profiles.
 *
 * usage:  php merge_data.php DATA_FILE_1 DATA_FILE_2
 */

if (count($argv) != 3) {
  fwrite(STDERR, "Please enter two file names to be merged (URLs are allowed)\n");
  exit(1);
}

// Get the input data.
$data1 = simplexml_load_file($argv[1]);
$data2 = simplexml_load_file($argv[2]);

$result = array(); // Internal structure to hold merged data

// Start with $data1 and build our temp storage array from this.
foreach ($data1 as $device) {
  // Data sources are an array of <device ua="...">
  $data = array();  // json data => count
  foreach ($device as $profile) {
    // <device> is an array of <profile count="x">
    $data[(string) $profile] = (int) $profile->attributes()->count;
  }
  $result[(string) $device->attributes()->ua] = $data;
}

// Now go through $data2 and merge into the tmp storage array.
foreach ($data2 as $device) {
  $ua = (string) $device->attributes()->ua;
  if (array_key_exists($ua, $result)) {
    // We've seen this device in the first data source.
    $data = $result[$ua];
  } else {
    $data = array();
  }
  foreach ($device as $profile) {
    $json = (string) $profile;
    $count = (int) $profile->attributes()->count;
    if (array_key_exists($json, $data)) { // Relies on the JSON being key sorted.
      // We've seen this device in the first data source so add counts together.
      $data[$json] += $count;
    } else {
      $data[$json] = $count;
    }
  }
  $result[$ua] = $data;
}


// Write the temp storage array out as XML.
$log = new SimpleXMLElement('<log/>');
foreach ($result as $ua => $profiles) {
  $device = $log->addChild('device');
  $device->addAttribute('ua', $ua);
  foreach ($profiles as $json => $count) {
    $profile = $device->addChild('profile', $json);
    $profile->addAttribute('count', $count);
  }
}
print($log->asXML());
