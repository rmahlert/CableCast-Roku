<?php

// CableCastRoku 0.0.2
// 6-16-14
// By Rob Mahlert
//
// Based off VODdisply.php by Ray Tiley and uses SimpleImage by Simon Jarvis
// 
// Turn off output buffering
ini_set('output_buffering', 'off');

// Print to screen informing this will take a bit..
// Remove after testing and using nightly cron
// echo "Processing...<br/>";
// ob_flush();
// flush();

// Call file to resize images
include('SimpleImage.php');

//Configure Section
$server = "http://xxx/"; // URL to CableCast server
$host = "http://xxx/"; // URL to webserver hosting files
$imageurl = "http://xxx/images/"; // URL to image files
$VODFile = "vod.xml"; // Name of XML file (needs to match in channel package files on Roku)
$xmlpath = "/xxx/xml/"; // path to xml file on server
$imagepath = "/xxx/images/"; // path to image directory
date_default_timezone_set('America/New_York');

// End Configure

// We need a count of the video files for the Roku 
	$counter = 0;

// Retrieve VOD data from CableCast
$client = new SoapClient($server . "CablecastWS/CablecastWS.asmx?WSDL");  // Creates New SOAP client using WSDL file

$searchDate = date("Y-m-d")."T12:00:00";

// Search for all shows that have an event date less than now that are available for VOD
$result = $client->AdvancedShowSearch(array(
    'ChannelID'        => 1,
    'searchString'         => '',
    'eventDate'           => date("Y-m-d") . "T00:00:00",
    'dateComparator'      => '<',
    'restrictToCategoryID'  => 17,  
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
    echo "ERROR- Check your server settings in the script.";
		// (Remove after testing and using nightly cron)
} else {
    // Create Roku XML for for VOD
$fh = fopen($xmlpath . $VODFile , 'w') or die("can't open file: " . $xmlpath . $VODFile);
ob_end_flush();
//Escape Characters
function xmlEscape($stringData) {
    return str_replace(array('&'), array('&amp;'), $stringData);
}
//Start XML Header
$stringData = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>' . "\n";
	   fwrite($fh, $stringData);
	   $stringData = "<feed>" . "\n";
	   fwrite($fh, $stringData);	   
	   $stringData = "<resultLength>TTLCOUNT</resultLength>" . "\n";
	   fwrite($fh, $stringData);
	   $stringData = "<endIndex>TTLCOUNT</endIndex>" . "\n";
	   fwrite($fh, $stringData);

// Write XML for each VOD file	   
    foreach($vods as $vod) {	  

	// Add to counter
	   $counter++;

    // Create thumbnail image URLs for Item
		$url = $server . "Cablecast/ShowFiles/" . $vod->ShowID."/_thumbnail-1.png" ;
		$imgHD = $imageurl . $vod->ShowID."_HD.png" ;
		$imgSD = $imageurl . $vod->ShowID."_SD.png" ;
	    $stringData = '<item sdImg="'.$imgSD . '"  hdImg="' . $imgHD . '">' . "/n";
	    fwrite($fh, $stringData);	 

	   // Write title of file
	   $stringData = "<title>". $vod->Title . "</title>" . "\n";
	   fwrite($fh, xmlEscape($stringData));

	   // Show ID
	   $stringData = "<contentId>". $vod->ShowID . "</contentId>" . "\n";
	   fwrite($fh, $stringData);

	   // Use Talk for default
	   $stringData = "<contentType>Talk</contentType>" . "\n";
	   fwrite($fh, $stringData);

	   // Content Quality SD or HD (Defualt to SD for now)
	   $stringData = "<contentQuality>SD</contentQuality>" . "\n";
	   fwrite($fh, $stringData);

	   // Stream type - Roku supports h.264
	   $stringData = "<streamFormat>mp4</streamFormat>" . "\n";
	   fwrite($fh, $stringData);

	   // Start stream information
	   $stringData = "<media>" . "\n";
	   fwrite($fh, $stringData);

	   // Stream Quality  SD or HD (Defualt to SD for now)
	   $stringData = "<streamQuality>SD</streamQuality>" . "\n";
	   fwrite($fh, $stringData);

	   // Stream bitrate (In VOD settings on Cablecast) Can it be pulled from VOD?
	   $stringData = "<streamBitrate>2000</streamBitrate>" . "\n";
	   fwrite($fh, $stringData);

	   // URL to CableCast VOD stream
	   $stringData = "<streamUrl>" . $vod->StreamingFileURL . "</streamUrl>" . "\n";
	   fwrite($fh, $stringData);

	   // End Media Tag section
	   $stringData = "</media>" . "\n";
	   fwrite($fh, $stringData);

	   // Description of the video
	   $stringData = "<synopsis>" . $vod->Comments ."</synopsis>" . "\n";
	   fwrite($fh, xmlEscape($stringData));

	   // Release date
	   $stringData = "<releasedate>".date('n/j/Y', strtotime($vod->EventDate))."</releasedate>" . "\n";
	   fwrite($fh, $stringData);

	   // Length of video in seconds	   
	   $stringData = "<runtime>". $vod->TotalSeconds . "</runtime>" . "\n";
	   fwrite($fh, $stringData);

	   // End Item tag for this video
	   $stringData = "</item>" . "\n";
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

	// For  little feedback while testing - note: will display when done processing ALL 
	// Remove after testing and using nightly cron
 	echo "Added VOD File:" . $vod->ShowID . "<br/>";
	ob_flush();
	flush();

    }

// Finish xml file
	   // end Feed
	   $stringData = "</feed>";
	   fwrite($fh, $stringData);
// Close xml file
fclose($fh);

$contents = file_get_contents($xmlpath . $VODFile);
$new_contents = str_replace('TTLCOUNT', $counter, $contents);
file_put_contents($xmlpath . $VODFile, $new_contents);

// List total number of files processed	(Remove after testing and using nightly cron)
echo "Finished, Total VOD Files:".$counter;
}