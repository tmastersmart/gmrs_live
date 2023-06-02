<?php
// (c)2023 by WXRB288 lagmrs.com  by pws.winnfreenet.com
// This script uses some code my weather programs. Member CWOP since 2015 
// Licensed only for GMRS,Allstar & Hamvoip nodes. All rights reserved. 

// settings to modify. Allows scripts to be updated at a later date without changing settings.

// Weather settings
// find your local MADIS station and airport go to the map https://madis-data.ncep.noaa.gov/MadisSurface/
// make sure all DATASETS are turned on and find the code your your station and your closest airport

// pull temp from mesowest, madis, APRSWXNET/Citizen Weather Observer Program (CWOP)
// For persional Weather Stations and Airports
//
// http://www.wxqa.com/  (CWOP) main website
// https://mesowest.utah.edu/
// https://madis-data.ncep.noaa.gov
// https://aprs.fi/

$station="E6758";// this is your local Station ID (CWOP)  EXXXX Starts with a E or a callsign (see map)
$level = 3 ;// 1 temp only 2=temp,cond 3= temp,cond,wind humi rain 



$zipcode="71432";// Zipcode for acuweather 

// https://alerts.weather.gov 
$skywarn="LAC043";// County Code (forcast and warnings. )  


// CPU temp settings
$reportAll = true; //  false= only over temp 
$nodeName = "server";// What name do you want it to use
//$nodeName = "system";// must be a file that exists in "/var/lib/asterisk/sounds"
//$nodeName = "node";// doesnt really work because it sounds like as node connect
$high = 60;// 85 is danger
$hot  = 50;


?>
