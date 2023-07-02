#!/usr/bin/php

<?php
// (c)2023 by WRXB288 and LAgmrs.com  
//
//   _____ _       __          __                 _____          _____  
//  / ____| |      \ \        / /                / ____|   /\   |  __ \ 
// | (___ | | ___   \ \  /\  / /_ _ _ __ _ __   | |       /  \  | |__) |
//  \___ \| |/ / | | \ \/  \/ / _` | '__| '_ \  | |      / /\ \ |  ___/ 
//  ____) |   <| |_| |\  /\  / (_| | |  | | | | | |____ / ____ \| |     
// |_____/|_|\_\\__, | \/  \/ \__,_|_|  |_| |_|  \_____/_/    \_\_|     
//               __/ |                                                  
//              |___/   
//
//   weather.gov 
//
//What is CAP?
//Common Alerting Protocol (CAP)  is an XML-based information standard used to facilitate emergency information sharing and data exchange across
// local, state, tribal, national and non-governmental organizations of different professions that provide emergency response and management services.
//
//NWS produces CAP for NWS weather and hydrologic alerts including watches, warnings, advisories, and special statements. 
//NWS CAP messages follow the CAP v1.2 standard defined by the Organization for the Advancement of Structured Information Standards (OASIS) and 
//comply with the Federal Emergency Management Agency (FEMA) Integrated Public Alert and Warning System (IPAWS) CAP profile.  
//The National Weather Service (NWS) CAP v1.2 Documentation provided on this site supplements the OASIS CAP v1.2 standard and IPAWS CAP
// profile by identifying the formats of NWS information contained within NWS CAP messages. 

//Uses of CAP
//NWS CAP can be used to launch Internet messages, trigger alerting systems, feed mobile device applications, news feeds, television/radio display
// and audio, digital highway signs, synthesized voice over automated telephone calls, and much more.


// The goal of this project is to control the software and how it works without using any prior closed source scripts in the node.
// a total rewrite allows me to control how my node works. I wrote this for myself but am releaseing it to other GMRS users.
// This is ground up written from scratch php using
//
// Notice this is only as reliable as the weather.gov server which goes down from time to time & your node.
// Not responiable for any failed reports use as is at your own risk.
//
//


$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_start = $mtime;

$path      ="/etc/asterisk/local/mm-software";
$outTmp    ="/tmp/skywarn.wav"; if (file_exists($outTmp)){unlink($outTmp);} 
$alertTxt  ="/tmp/skywarn.txt"; 
$alertTxtHeadline ="/tmp/skywarn_headline.txt"; 
$clash     ="/tmp/mmweather-task.txt";

//compatibality  
$path1="/tmp/AUTOSKY";$path2="/tmp/AUTOSKY/WXA";
$tailfile  ="$path2/wx-tail.wav";  

if(!is_dir($path1)){ mkdir($path1, 0755);}
if(!is_dir($path2)){ mkdir($path2, 0755);}

$ver= "v1.9";
  
include ("$path/load.php");
include ("$path/sound_db.php");
include ("$path/check_reg.php");


// do not change this its only for debugging 
//$skywarn="ARC111";// for live testing ONLY. Find a zone with an alert and enter it.

// Get php timezone in sync with the PI
$line =	exec('timedatectl | grep "Time zone"'); //       Time zone: America/Chicago (CDT, -0500)
$line = str_replace(" ", "", $line);
$pos1 = strpos($line, ':');$pos2 = strpos($line, '(');
if ($pos1){  $zone   = substr($line, $pos1+1, $pos2-$pos1-1); }
else {$zone="America/Chicago";}
define('TIMEZONE', $zone);
date_default_timezone_set(TIMEZONE);
$phpzone = date_default_timezone_get(); // test it 
if ($phpzone == $zone ){$phpzone="$phpzone set";}
else{$phpzone="$phpzone ERROR";}
$phpVersion= phpversion();
$time= date('H:i');
$date =  date('m-d-Y');
$datum   = date('m-d-Y H:i:s');
$gmdatum = gmdate('m-d-Y H:i:s');
print "
===================================================
Cap Warn CAP 1.1/1.2 NWS API    $coreVersion-c$ver
(c)2023 WRXB288 LAGMRS.com all rights reserved
$phpzone PHP v$phpVersion
===================================================
$datum Node:$node UTC:$gmdatum 
";
$action=""; 
// We dont want 2 voices at the same time playing
// This could happen if cron calls 2 at the same time.
if(file_exists($clash)){unlink($clash);
$out="Pausing for other thread(s) to finish";
print "$datum $out
"; save_task_log ($out);
sleep(25);
}



include("$path/forcast.php"); // Read the new API.
// returns  $apiVer,$description,$headline,$event,$icon,$shortForcast,$detailedForecast,

$html = str_replace('"', "", $html);
$pos = strpos($html, "features: []");if ($pos){$event="clear";}


// Check the reg -----------------
reg_check ("check");// $node1 $ip $port2 $registered
if($registered =="Registered"){watchdog ("okreg");}
if($registered !="Registered"){
watchdog ("reg");// add to counter
check_gsm_db ("an-error-has-occured");if($file1){$action = "$action $file1";}
check_gsm_db ("node");if($file1){$action = "$action $file1";} 
$oh=false;
$x = (string)$node1;
for($i=0;$i<strlen($x);$i++)
 { 
make_number ($x[$i]); 
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
}
check_gsm_db ("is-not-registered");if($file1){$action = "$action $file1";} 

$pos1 = strpos("-$registered", 'Refused');
if($pos1){
check_gsm_db ("rejected");if($file1){$action = "$action $file1";} 
}

// if no events we need to talk seperate from alert.
if($event=="clear"){
$file = $outTmp;
exec ("sox $action $file",$output,$return_var);
$datum   = date('m-d-Y H:i:s');
print "$datum Playing file $file
";
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/skywarn'",$output,$return_var);
}


// Do my repair fix if counter says to 
if($counter >$watchdog and $NotReg){
reg_fix ("check");
if(!$NotReg){$out="We are back online"; save_task_log ($out);print "$datum $out
";}
}
}





if ($event=="clear"){line_end("There are no active events");}
if (!$event){$out="Error Bad CAP data";save_task_log ($out);line_end($out);}


if (file_exists($alertTxt)){
$test = file_get_contents($alertTxt);
if ($test == $event){line_end("No changes detected");}

print "$datum Change detected  
";
save_task_log ("new Alert $event"); 
}
$fileOUT = fopen($alertTxt,'w');fwrite ($fileOUT,$event);fclose ($fileOUT);
$fileOUT = fopen($alertTxtHeadline,'w');fwrite ($fileOUT,$headline);fclose ($fileOUT);


$file = $tailfile;if(file_exists($file)){unlink($file);} // kill the last tail file to prevent it launching during play


check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
check_wav_db  ("strong click");                if($file1){$action = "$action $file1";}
check_wav_db  ("updated weather information"); if($file1){$action = "$action $file1";}
check_wav_db  ("weather service"); if($file1){if($file1){$action = "$action $file1";}}

$u= explode(",",$event); 

foreach($u as $line){
check_wav_db  ("star dull"); if($file1){$action = "$action $file1";}
check_wav_db  ($line); if($file1){$action = "$action $file1";}
check_wav_db  ("light click"); if($file1){$action = "$action $file1";}

// check for major warrnings.
if($line=="Tornado Warning" or $line=="Blizzard Warning" or $line=="Hurricane Warning" or $line=="Severe Thunderstorm Warning" ){
 check_gsm_db ("advised to seek shelter");if($file1){$action = "$action $file1";}
  }

}



$file = $outTmp;
exec ("sox $action $file",$output,$return_var);
$datum   = date('m-d-Y H:i:s');
print "$datum Playing file $file
";
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/skywarn'",$output,$return_var);


// build the shorter tail file
$action="";
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
$u= explode(",",$event);
foreach($u as $line){
check_wav_db  ("star dull"); if($file1){$action = "$action $file1";}
check_wav_db  ($line); if($file1){$action = "$action $file1";}
check_wav_db  ("light click"); if($file1){$action = "$action $file1";}
}
$file = $tailfile;
exec ("sox $action $file",$output,$return_var);
print "$datum Tailfile updated $file
";


line_end("Finished");













function all_clear($in){
global $file1,$file,$alertTxt,$tailfile,$node,$outTmp;
//$alertTxt  ="/tmp/AUTOSKY/warnings.txt"; 
$action="";
$datum   = date('m-d-Y H:i:s');
print "$datum Processing all Clear
";
$outTmp="/tmp/skywarn.wav"; // dupe
$file= $outTmp;if (file_exists($file)){unlink($file);}

if (file_exists($alertTxt)){unlink($alertTxt); // There has been a past alert so we need to clear it and notify
if (file_exists($tailfile)){unlink($tailfile);}
check_wav_db  ("silence1");                    if($file1){$action = "$action $file1";}
check_wav_db  ("updated weather information"); if($file1){$action = "$action $file1";}
check_wav_db  ("strong click");                if($file1){$action = "$action $file1";}
check_wav_db  ("clear");                       if($file1){$action = "$action $file1";}
check_wav_db  ("star dull");                   if($file1){$action = "$action $file1";}
exec ("sox $action $outTmp",$output,$return_var);

$status ="Playing all clear $file";save_task_log ("All Events now Clear");print "$datum $status
";

$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/skywarn'",$output,$return_var);
if(!$status){$status="OK";}
}

if (!file_exists($tailfile)){
$file = $tailfile; 
check_wav_db  ("silence1");if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
$datum   = date('m-d-Y H:i:s');
print "$datum tail file reset to silence. $tailfile
";

}


}




?>
