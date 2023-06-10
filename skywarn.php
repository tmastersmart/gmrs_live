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
//https://alerts.weather.gov 
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
//NWS notice
//NWS CAP v1.1 is being terminated. NWS CAP users should instead use CAP v1.2. Please see Service Change Notice
//(SCN) 17-35 for information about termination. See the NWS Common Alerting Protocol page for NWS sources of 
//CAP v1.2 and updated documentation. An updated SCN will be released in the second half of 2022 with the 
//projected termination date.
//
//
// Notice this is only as reliable as the https://alerts.weather.gov server which goes down from time to time & your node.
// Not responiable for any failed reports use as is at your own risk.
//
//

$path      ="/etc/asterisk/local/mm-software";
$xml       ="/tmp/skywarn.xml";

$outTmp    ="/tmp/skywarn.wav";


//compatibality , after a reboot the path needs resetting 
$path1="/tmp/AUTOSKY";$path2="/tmp/AUTOSKY/WXA";
$tailfile  ="$path2/wx-tail.wav";  
$alertTxt  ="$path1/warnings.txt"; 
if(!is_dir($path1)){ mkdir($path1, 0755);}
if(!is_dir($path2)){ mkdir($path2, 0755);}

$ver= "v1.3";

$testNewApi=false; // Future use. Still testing. This is for the NWS new system.
  
include ("$path/config.php");
include ("$path/sound_db.php");
$file="$path/sound_wav_db.csv";
$soundDbWav = file($file);
$soundDbGsm ="";
$soundDbUlaw="";

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

// automatic node setup
$file= "$path/mm-node.txt";
if(!file_exists($file)){create_node ($file);}
if(file_exists($file)){
$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$u= explode(",",$line);$node=$u[0];
}
if (!$node){
$datum = date('m-d-Y-H:i:s');
print"$datum Error loading node number $line Place node number in $file 1988,1988,";die;}
}





$phpVersion= phpversion();

$time= date('H:i');
$date =  date('m-d-Y');
print "
===================================================
SkyWarn alerts.weather.gov $ver
(c)2023 WRXB288 LAGMRS.com all rights reserved
$phpzone PHP v$phpVersion NODE:$node
===================================================
";

//https://api.weather.gov/zones/county/LAC043

$domain ="alerts.weather.gov"; $url = "cap/wwaatmget.php?x=$skywarn"; 
$datum  = date('m-d-Y H:i:s');
print "$datum Polling $skywarn $domain >";
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $poll_start = $mtime;
$file=$xml;
$options = array(
    'http'=>array(
        'timeout' => 10,  //if it takes this long somethings wron
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
                  "Cookie: foo=bar\r\n" .
                  "User-Agent: Mozilla/5.0 (iPad; U; CPU iPad OS 5_0_1 like Mac OS X; en-us)   AppleWebKit/535.1+ (KHTML like Gecko) Version/7.2.0.0 Safari/6533.18.5\r\n" // wont work unless we fake this )
));
$context = stream_context_create($options);
$html = @file_get_contents("https://$domain/$url",false,$context);



if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$html);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
//$html = str_replace('"', "", $html);
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);
$poll_time = round($poll_time,2);


//$file= "$path/test.xml";// Testing file with alerts.
if(!file_exists($file)){print "ERROR missing $file";unset ($soundDbWav);die;}

$clear=false;$test = read_cap($file);
if (!$data_good){
$status="data bad";if($poll_time>=10){$status="timeout";}
$datum  = date('m-d-Y H:i:s');
print "<$status>$poll_time Sec. (data bad)
===================================================
";
unset ($soundDbWav);die;
}
// data is good
print "<ok>$poll_time Sec. 
"; 

if ($testNewApi){include("$path/forcast.php");}





if($title){// Testing title should be long form alert
print "$datum $title 
";
}
if($clear==true){
$datum  = date('m-d-Y H:i:s');
print "$datum There are no active watches, warnings or advisories 
";  
all_clear("clear");
print"===================================================
";
unset ($soundDbWav);die;
} 




// clear unwanted alerts
if (!$sayWarn){
$test="Warning";// included for testing likely be removed
$pos = strpos($warn1, $test);if($pos){$warn1="";}
$pos = strpos($warn2, $test);if($pos){$warn2="";}
$pos = strpos($warn3, $test);if($pos){$warn3="";}
$pos = strpos($warn4, $test);if($pos){$warn4="";}
}

if (!$sayWatch){
$test="Advisory";
$pos = strpos($warn1, $test);if($pos){$warn1="";}
$pos = strpos($warn2, $test);if($pos){$warn2="";}
$pos = strpos($warn3, $test);if($pos){$warn3="";}
$pos = strpos($warn4, $test);if($pos){$warn4="";}
}

if (!$sayStatement){
$test="Statement";
$pos = strpos($warn1, $test);if($pos){$warn1="";}
$pos = strpos($warn2, $test);if($pos){$warn2="";}
$pos = strpos($warn3, $test);if($pos){$warn3="";}
$pos = strpos($warn4, $test);if($pos){$warn4="";}
}

// we have a CAP now process it
$newAlert="[";
if($warn1){print "$datum $warn1  
";$newAlert="$newAlert$warn1";}
if($warn2){print "$datum $warn2 
";$newAlert="$newAlert,$warn2";}                      
if($warn3){print "$datum $warn3 
";$newAlert="$newAlert,$warn3";}
if($warn4){print "$datum $warn4 
";$newAlert="$newAlert,$warn4";}
$newAlert="$newAlert]";
// test for dupe

if (file_exists($alertTxt)){
$test = file_get_contents($alertTxt);
 if ($test == $newAlert){
 $datum  = date('m-d-Y H:i:s');
 print "$datum No changes detected 
";
unset ($soundDbWav);die; 
 }
 print "$datum Change detected 
"; 
}

// update the textfile for supermon
$fileOUT = fopen($alertTxt,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$newAlert);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$update=true; $action=""; 

$file = $tailfile;if(file_exists($file)){$update=false;unlink($file);} // kill the last tail file to prevent it launching during play

$file = $outTmp;  if (file_exists($file)){unlink($file);} //set out outfile and kill any old one.
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
if ($update){check_wav_db  ("updated weather information"); if($file1){$action = "$action $file1";}} // Only play if first alert

if($warn1<>""){
check_wav_db  ("star dull"); if($file1){$action = "$action $file1";}
check_wav_db  ($warn1); if($file1){$action = "$action $file1";}
check_wav_db  ("light click"); if($file1){$action = "$action $file1";}
}
if($warn2<>""){
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
check_wav_db  ($warn2); if($file1){if($file1){$action = "$action $file1";}}
check_wav_db  ("light click"); if($file1){if($file1){$action = "$action $file1";}}

}
if($warn3<>""){
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
check_wav_db  ($warn3); if($file1){if($file1){$action = "$action $file1";}}
check_wav_db  ("light click"); if($file1){if($file1){$action = "$action $file1";}}
}
if($warn4<>""){
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
check_wav_db  ($warn4); if($file1){if($file1){$action = "$action $file1";}}
check_wav_db  ("light click"); if($file1){if($file1){$action = "$action $file1";}}

}
check_wav_db  ("weather service"); if($file1){if($file1){$action = "$action $file1";}}

exec ("sox $action $file",$output,$return_var);
$datum   = date('m-d-Y H:i:s');
print "$datum Playing file $file
";
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/skywarn'",$output,$return_var);
if(!$status){$status="OK";}

// build the shorter tail file
$file = $tailfile;$action="";
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}

if($warn1<>""){
check_wav_db  ("star dull"); if($file1){$action = "$action $file1";}
check_wav_db  ($warn1); if($file1){$action = "$action $file1";}
check_wav_db  ("light click"); if($file1){$action = "$action $file1";}
}
if($warn2<>""){
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
check_wav_db  ($warn2); if($file1){if($file1){$action = "$action $file1";}}
check_wav_db  ("light click"); if($file1){if($file1){$action = "$action $file1";}}

}
if($warn3<>""){
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
check_wav_db  ($warn3); if($file1){if($file1){$action = "$action $file1";}}
check_wav_db  ("light click"); if($file1){if($file1){$action = "$action $file1";}}
}
if($warn4<>""){
check_wav_db  ("silence1"); if($file1){$action = "$action $file1";}
check_wav_db  ($warn4); if($file1){if($file1){$action = "$action $file1";}}
check_wav_db  ("light click"); if($file1){if($file1){$action = "$action $file1";}}
}
exec ("sox $action $file",$output,$return_var);

print "$datum Tailfile updated $file
$datum finished  $status $return_var
===================================================
";

unset ($soundDbWav);die;







// decode the xml 
function read_cap ($file) {
global $warn1,$warn2,$warn3,$warn4,$data_good,$title,$clear,$file,$out,$summary1,$summary2,$summary3,$summary4;

$html= file($file);$out=$html;$data_good = false; $clear=false;
$warn1="";$warn2="";$warn3="";$warn4="";$summary1="";$summary2="";$summary3="";$summary4="";

foreach($html as $line){

$pos = strpos($line, '>There are no active watches'); //<title>There are no active watches, warnings or advisories</title>     
if($pos == true){//print $line;
$clear=true;$data_good=true;
return;}

$pos1 = strpos($line, 'title>');// title contains long format
$stop=false; 
if ($pos1) {
     $Lpos = strpos($line, '>');$Rpos = strpos($line, '</title>');
     $found = substr($line, $Lpos+1,$Rpos-$Lpos-1);
     $title=$found;
     }
     
// <cap:event> there apears to be 2 diffrent formats Unsire if this is CAP 1.1     
$pos2 = strpos($line, 'cap:event>');
$stop=false;
if ($pos2) {
     $Lpos = strpos($line, '>');$Rpos = strpos($line, '</cap:event>');
     $found = substr($line, $Lpos+1,$Rpos-$Lpos-1);//print "cap $found";
     $data_good = true;$clear=false;
     if (!$warn1){$warn1=$found;$stop=true;}
     if(!$stop){if (!$warn2){$warn2=$found;$stop=true;} }
     if(!$stop){if (!$warn3){$warn3=$found;$stop=true;} }
     if(!$stop){if (!$warn4){$warn4=$found;$stop=true;} }
     continue;   
     }

// <event>     
$pos3 = strpos($line, 'event>'); // <event>SEVERE THUNDERSTORM</event> This is CAP 1.2
$stop=false;
if ($pos3) {
     $Lpos = strpos($line, '>');$Rpos = strpos($line, '</event>');
     $found = substr($line, $Lpos+1,$Rpos-$Lpos-1);//print "event $found";
     $data_good = true;$clear=false;
     if (!$warn1){$warn1=$found;$stop=true;}
     if(!$stop){if (!$warn2){$warn2=$found;$stop=true;} }
     if(!$stop){if (!$warn3){$warn3=$found;$stop=true;} }
     if(!$stop){if (!$warn4){$warn4=$found;$stop=true;} }   
     }

//<summary>...A strong thunderstorm will impact portions xxx through 745 PM CDT... At 644 PM CDT, Doppler radar was tracking a strong thunderstorm 13 miles west xxx or 21 miles southeast of xxx moving northwest at 35 mph.</summary>
// These summarys will show up with a statement.
$pos3 = strpos($line, 'summary>');
$stop=false;
if ($pos3) {
     $Lpos = strpos($line, '>');$Rpos = strpos($line, '</summary>');
     $found = substr($line, $Lpos+1,$Rpos-$Lpos-1);//print "event $found";
     $data_good = true;$clear=false;
     if (!$summary1){$summary1=$found;$stop=true;}
     if(!$stop){if (!$summary2){$summary2=$found;$stop=true;} }
     if(!$stop){if (!$summary3){$summary3=$found;$stop=true;} }
     if(!$stop){if (!$summary4){$summary4=$found;$stop=true;} }   
     }
// My notes on format 

//<cap:status>Actual</cap:status>
//<cap:msgType>Alert</cap:msgType>
//<cap:category>Met</cap:category>
//<cap:urgency>Expected</cap:urgency>
//<cap:severity>Minor</cap:severity>
//<cap:certainty>Likely</cap:certainty>
//<cap:areaDesc>Delta</cap:areaDesc>


//CAP v1.2 standard
// https://www.weather.gov/media/alert/CAP_v12_guide_05-16-2017.pdf
//<alert xmlns = “urn:oasis:names:tc:emergency:cap:1.2”>
//<responseType>responseType</responseType> Shelter,Evacuate,Prepare,Execute,Avoid,Monitor,Assess,AllClear,None
//<certainty>certainty</certainty>          Observed,Likely,Possible,Unlikely,Unknown
//<severity>severity</severity>             Extreme,Severe,Moderate,Minor,Unknown
//<urgency>urgency</urgency>                Immediate,Expected,Future,Past,Unknown
//<responseType>responseType</responseType> Shelter,Evacuate,Prepare,Execute,Avoid,Monitor,Assess,AllClear,None
//<description>description</description> 
//<instruction>instruction</instruction> 
// <eventCode>:
//TOR (Tornado Warning)
//SVR (Severe Thunderstorm Warning)
//SVS (Severe Weather Statement)
//SMW (Special Marine Warning)
//MWS (Marine Weather Statement)
//EWW (Extreme Wind Warning)

//<parameter>
// <valueName>windGust</valueName>
// <value>speed</value>
//</parameter>
 
}               


//dupe check
if($warn1==$warn2){$warn2="";}
if($warn1==$warn3){$warn3="";}
if($warn1==$warn4){$warn4="";}
if($warn2==$warn3){$warn3="";}
if($warn2==$warn4){$warn4="";}
if($warn3==$warn4){$warn4="";}


}

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
$datum   = date('m-d-Y H:i:s');
print "$datum Playing all clear $file
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


function create_node ($file){
global $file,$path;
$line= exec("cat /usr/local/etc/allstar_node_info.conf  |egrep 'NODE1='",$output,$return_var);
$line = str_replace('"', "", $line);
$u= explode("=",$line);
$node=$u[1];
$file= "$path/mm-node.txt";
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node, , , , ");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);
}
?>
