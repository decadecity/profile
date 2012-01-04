DeviceAtlas Cloud

***** INTRO *****
DeviceAtlas Cloud is a web service which can return device information such
as screen width, screen height, is mobile, vendor, model etc. To see a full
list of properties in DeviceAtlas please visit http://deviceatlas.com .

The PHP client API provides an easy way to query DeviceAtlas Cloud. It 
provides the ability to cache returned data locally to greatly improve 
performance.



***** CACHING *****
The client API can cache returned data to speed up subsequent requests. It
can use both a file cache and a per user cookie cache. It is recommended to
always use the cache.



***** CONFIG ***** 
The DeviceAtlas Cloud client is configured by setting the properties at the 
top of the Client.php file. The only required property is your DeviceAtlas
licence key.



***** EXAMPLE USAGE *****
It is very easy to use the Client API. It simply needs to be included at
the top of the page and can be queried by doing the following:

$da_data = DeviceAtlasCloudClient::getDeviceData();

The included example.php file uses the client API to query DeviceAtlas Cloud
and displays a webpage with all the returned properties. 

The Client API uses your device's User-Agent to determine what device it
is. If you are testing using a desktop web browser it is recommended to use a
User-Agent switcher plugin to modify the browser's User-Agent.

Alternatively, simply passing TRUE to getDeviceData() will make the client
API use a built in test User-Agent.


