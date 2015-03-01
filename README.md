CableCast-Roku
==============

This script will pull the VOD information off of a Tightrope CableCast server and write a xml for a Roku Channel.

You also need to create the Roku Channel package.. (working on a basic channel)

Rough draft of instructions.. I don't code or write docs good!

Open the CableCast-Roku.php file in an editor like notepad++ (http://notepad-plus-plus.org/)

Edit the lines below in the 'Configure Section' of the CableCast-Roku.php file
$server = "http://www.URLorIPtoCableCast.com/"; -  URL or IP address to CableCast server with trailing '/'
$host = "http://www.yoursite.com/";  -  URL to the webserver hosting the xml files with trailing '/'
$imageurl = "http://www.yoursite.com/roku/images/";  -  URL to the webserver hosting the image files 
$xmlpath = "/home/username/public_html/roku/xml/";  - Path to xml file(s) on webserver. (Same location as categories.xml)
$imagepath = "/home/username/public_html/roku/images/";  - path to the image directory on the webserver

Enter the search terms in the '$category' array in the config section. these should be the same names in your categories.xml file!
Example: $category = array("Selectmen", "Health", "Open", "Aging", "Assessors", "Cable", "Finance", "Planning", "Conservation", "Uncommon");
$category = array("Enter", "Your", "Words"); 

Set your Time Zone - http://php.net/manual/en/timezones.america.php
date_default_timezone_set('America/New_York');

That's it to configure, Save the file.

Upload the CableCast-Roku.php and SimpleImage.php files onto your server. Make sure they are in the same directory and with 755 permissions. 
I have my setup in directory named 'roku' and keep the two files and the images and xml directories under that. 

Setup a cronscipt to run the php file daily to update your xml files.
You can also just point your browser to the file to run the file. 
