<?php

// CableCastRoku 0.0.5
// 2-28-15
// By Rob Mahlert
// With additions by
// Based off VODdisply.php by Ray Tiley and uses SimpleImage by Simon Jarvis
// 


// Configure Section ******************************************************************************************************************************
$server = "http://www.URLorIPtoCableCast.com/"; // URL or IP address to CableCast server with trailing '/'
$host = "http://www.yoursite.com/"; // URL to the webserver hosting the xml files with trailing '/'
$imageurl = "http://www.yoursite.com/roku/images/"; // URL to the webserver hosting the image files 
$xmlpath = "/home/username/public_html/roku/xml/"; // Path to xml file(s) on webserver. (Same location as categories.xml)
$imagepath = "/home/username/public_html/roku/images/"; // path to the image directory on the webserver

//  This will search for VOD files in the CableCast Title with the word(s) listed in the array and create a xml file with that word as the name. 
//  Be precise as possiple for the search term. Example, for Board of Health, use "Health". 
//  Important - the search term need to match EXACTLY what is used for the xml file in the category.xml file!)
//  If the script does not create the xml file for a search term, check your spelling. It will fail if it does not find anything with that in the search.
//  Enter the search term for each category within array() in quotes array with a comma inbetween each, Eaxmple array("Assessors", "Health") 
//  Remonder: Make sure these are also in the catergories.xml!!!
//  Example: $category = array("Selectmen", "Health", "Open", "Aging", "Assessors", "Cable", "Finance", "Planning", "Conservation", "Uncommon"); 
$category = array("Enter", "Your", "Words"); 

// Set your Time Zone - http://php.net/manual/en/timezones.america.php
date_default_timezone_set('America/New_York');

// End Configure Section ******************************************************************************************************************************

// Call file to resize images
include('SimpleImage.php');

//Escape Characters
function xmlEscape($stringData) {
	return str_replace(array('&'), array('&amp;'), $stringData);
}

$arrlength = count($category);
//Main loop
for($x = 0; $x < $arrlength; $x++) {

	// Name VOD XML file from Category
	$VODFile = $category[$x] . ".xml";

	// We need a count of the video files for the Roku 
	$counter = 0;

	// Retrieve VOD data from CableCast
	$client = new SoapClient($server . "CablecastWS/CablecastWS.asmx?WSDL");  // Creates New SOAP client using WSDL file

	$searchDate = date("Y-m-d")."T12:00:00";

	// Search for all shows that have an event date less than now that are available for VOD
	$result = $client->AdvancedShowSearch(array(
	'ChannelID'        => 1,
	'searchString'         => $category[$x],
	'eventDate'           => date("Y-m-d") . "T00:00:00",
	'dateComparator'      => '<',
	'restrictToCategoryID'  => 0,  
	'restrictToProducerID'   => 0, 
	'restrictToProjectID'    =>  0,
	'displayStreamingShowsOnly'   =>  1,
	'searchOtherSites'     =>   0,));

	if(!isset($result->AdvancedShowSearchResult->SiteSearchResult->Shows->ShowInfo)) {
		$vods = array();
	} else {
		$vods = is_array($result->AdvancedShowSearchResult->SiteSearchResult->Shows->ShowInfo) ?
		$result->AdvancedShowSearchResult->SiteSearchResult->Shows->ShowInfo :
		array($result->AdvancedShowSearchResult->SiteSearchResult->Shows->ShowInfo);
	}

	if(count($vods) == '0') {
		//There is probably something wrong if this shows up.
		echo "ERROR- Check your server settings in the script. - " . "Cat: " . $category[$x] . " file: " . $VODFile . "</br>\n";
		// (Remove after testing and using nightly cron)
	} else {
		// Create Roku XML for for VOD
		$fh = fopen($xmlpath . $VODFile, 'w') or die("can't open file: " . $xmlpath . $VODFile);
		
		// ob_end_flush();


		//Start XML Header
		$stringData = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>' . "\n";
		fwrite($fh, $stringData);
		$stringData = "<feed>" . "\n";
		fwrite($fh, $stringData);	   
		$stringData = "     <resultLength>TTLCOUNT1</resultLength>" . "\n";
		fwrite($fh, $stringData);
		$stringData = "     <endIndex>TTLCOUNT2</endIndex>" . "\n";
		fwrite($fh, $stringData);

		// Write XML for each VOD file	   
		foreach($vods as $vod) {	  

			// Add to counter
			$counter++;

			// Create thumbnail image URLs for Item
			$url = $server . "Cablecast/ShowFiles/" . $vod->ShowID."/_thumbnail-1.png" ;
			$imgHD = $imageurl . $vod->ShowID."_HD.png" ;
			$imgSD = $imageurl . $vod->ShowID."_SD.png" ;
			$stringData = '          <item sdImg="'.$imgSD . '"  hdImg="' . $imgHD . '">' . "\n";
			fwrite($fh, $stringData);	 

			// Write title of file
			$stringData = "               <title>". $vod->Title . "</title>" . "\n";
			fwrite($fh, xmlEscape($stringData));

			// Show ID
			$stringData = "                <contentId>". $vod->ShowID . "</contentId>" . "\n";
			fwrite($fh, $stringData);

			// Use Movie for default
			$stringData = "                <contentType>Movie</contentType>" . "\n";
			fwrite($fh, $stringData);

			// Content Quality SD or HD (Defualt to SD for now)
			$stringData = "               <contentQuality>SD</contentQuality>" . "\n";
			fwrite($fh, $stringData);

			// Stream type - Roku supports h.264
			$stringData = "               <streamFormat>mp4</streamFormat>" . "\n";
			fwrite($fh, $stringData);

			// Start stream information
			$stringData = "               <media>" . "\n";
			fwrite($fh, $stringData);

			// Stream Quality  SD or HD (Defualt to SD for now)
			$stringData = "                    <streamQuality>SD</streamQuality>" . "\n";
			fwrite($fh, $stringData);

			// Stream bitrate (In VOD settings on Cablecast) Can it be pulled from VOD?
			$stringData = "                    <streamBitrate>2000</streamBitrate>" . "\n";
			fwrite($fh, $stringData);
			
			// URL to CableCast VOD stream
			$stringData = "                    <streamUrl>" . $vod->StreamingFileURL . "</streamUrl>" . "\n";
			fwrite($fh, $stringData);

			// End Media Tag section
			$stringData = "               </media>" . "\n";
			fwrite($fh, $stringData);

			// Description of the video <Description> old tag :<synopsis>
			$stringData = "               <Description> " . $vod->Comments . " *</Description>" . "\n";
			fwrite($fh, xmlEscape($stringData));

			// Release date
			$stringData = "               <releasedate>".date('n/j/Y', strtotime($vod->EventDate))."</releasedate>" . "\n";
			fwrite($fh, $stringData);

			// Length of video in seconds	   
			$stringData = "               <runtime>". $vod->TotalSeconds . "</runtime>" . "\n";
			fwrite($fh, $stringData);

			// End Item tag for this video
			$stringData = "          </item>" . "\n";
			fwrite($fh, $stringData);

			// End writing VOD

			// Load thumbnail and resize for Roku HD and SD image files

			// Change to local path for images
			$imgHD = $imagepath . $vod->ShowID."_HD.png" ;
			$imgSD = $imagepath . $vod->ShowID."_SD.png" ;

			// Retrieve image off CableCast and resize
			$image = new SimpleImage();
			
			// HD image exist?
			if (file_exists($imgHD)) {
			} else {
				$image->load($url);
				$image->resize(290,218);
				$image->save($imgHD);
			}	
			if (file_exists($imgSD)) {
			} else {		
				$image->resize(214,144);
				$image->save($imgSD);
			}		

			// Back to next video file

		}

		// Finish xml file
		// end Feed
		$stringData = "</feed>";
		fwrite($fh, $stringData);
		// Close xml file
		fclose($fh);

		$contents = file_get_contents($xmlpath . $VODFile);
		$new_contents = str_replace('TTLCOUNT1', $counter, $contents);
		$output = str_replace('TTLCOUNT2', $counter, $new_contents);



		file_put_contents($xmlpath . $VODFile, $output);

		// List total number of files processed	(Remove after testing and using nightly cron)
		echo "Finished " . $VODFile . ", Total VOD Files: ".$counter . "<br>";
	}
}