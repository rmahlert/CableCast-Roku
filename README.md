CableCast-Roku
==============
0.0.5
2-28-15 (quick update 9/13/15)

This is a rough draft of instructions.. I don't code or write docs very good!

This script will pull the VOD information off of a Tightrope CableCast server and write a xml file for a Roku Channel.
Just in case, must have a Cablecast Pro VOD!. It is also possible to add the stream from a Cablecast Live to your Roku channel.

PLEASE NOTE: You also need to create the Roku Channel package! I have not had time to provide a basic Roku channel package. 
I used and suggest checking out http://www.herofish.com/2012/01/how-to-create-you-own-roku-videoplayer-channel/ to get an idea on how to create a basic Roku channel. 

To use this script:

Open the CableCast-Roku.php file in an editor like notepad++ (http://notepad-plus-plus.org/)

Edit the lines below in the 'Configure Section' of the CableCast-Roku.php file
$server = "http://www.URLorIPtoCableCast.com/"; -  URL or IP address to CableCast server with trailing '/'
$host = "http://www.yoursite.com/";  -  URL to the webserver hosting the xml files with trailing '/'
$imageurl = "http://www.yoursite.com/roku/images/";  -  URL to the website hosting the image files 
$xmlpath = "/home/username/public_html/roku/xml/";  - Path to xml file(s) on webserver. (Same location as categories.xml)
$imagepath = "/home/username/public_html/roku/images/";  - path to the image directory on the webserver

Enter the search terms in the '$category' array in the config section. these should be the same names in your categories.xml file!
Example: $category = array("Selectmen", "Health", "Open", "Aging", "Assessors", "Cable", "Finance", "Planning", "Conservation", "Uncommon");
$category = array("Enter", "Your", "KeyWords"); 


Set your Time Zone - http://php.net/manual/en/timezones.america.php
date_default_timezone_set('America/New_York');

That's it to configure, Save the file.

Upload the CableCast-Roku.php and SimpleImage.php files onto your web server. Make sure they are in the same directory and with 755 permissions. 
I have my setup in directory named 'roku' and keep the two files and the images and xml directories under that. Those directories also to write permissions to save the xml and save the images.

Setup a cron script to run the php file daily to update your xml files. Or you will need to manually call the script in a browser to update the Roku channel.





