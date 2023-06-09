#!/usr/bin/php
<?php
// (c)2015/2023 by The Master lagmrs.com  by pws.winnfreenet.com
// This script uses some code my weather programs. Member CWOP since 2015 
// Licensed only for GMRS,Allstar & Hamvoip nodes. All rights reserved. 
//
// _______ _                 __          __        _   _                  _____             
//|__   __(_)                \ \        / /       | | | |                / ____|            
//   | |   _ _ __ ___   ___   \ \  /\  / /__  __ _| |_| |__   ___ _ __  | |     _ __  _   _ 
//   | |  | | '_ ` _ \ / _ \   \ \/  \/ / _ \/ _` | __| '_ \ / _ \ '__| | |    | '_ \| | | |
//   | |  | | | | | | |  __/    \  /\  /  __/ (_| | |_| | | |  __/ |    | |____| |_) | |_| |
//   |_|  |_|_| |_| |_|\___|     \/  \/ \___|\__,_|\__|_| |_|\___|_|     \_____| .__/ \__,_|
//                                                                             | |          
//                                                                             |_|          
// 
// pull temp from mesowest, madis, APRSWXNET/Citizen Weather Observer Program (CWOP)
// For persional Weather Stations and Airports
//
// http://www.wxqa.com/  (CWOP) main website
// https://mesowest.utah.edu/
// https://madis-data.ncep.noaa.gov
// https://aprs.fi/
// 
// v1.6 06/01/2023 This is the first release with a mostly automated setup and installer.
// v1.7 06/02/2023 Debugging after moving to seperate subdirectory. 
// v1.8 06/03/2023 No soundfile for thunderstorms rename without s
// 
// This uses the authors token. At this time only authors need to get tokens
// not you. This token may change in a later version.
//
// If you have a station this will read your data from mesowest or of you dont you can read from
// Any local station. This provides more currect and accurate data than provided by the name brand
// companies. 
// In my case I wanted my stations data being use and have no ideal where the current script was getting
// the incorrect temp from. 
// This gives you direct access to local stations bypassing the middle man weather.com and acuweather.
//
// You will have to look at the madis map and find a local station or use yours.
// You also need to find your local airport code so that we can get current weather because stations do provide that data.
//
// This script replaces a perl and batch file in favor of PHP
//
// find your local MADIS station and airport go to the map https://madis-data.ncep.noaa.gov/MadisSurface/
// make sure all DATASETS are turned on and find the code your your station and your closest airport
//
// Contact me if you have a local station running AmbientWeather ObserverIP and wish to submit weather data to CWOP.
// I am testing a new PHP version that will run on a PI..... pws.winnfreenet.com 
//
// place in  /etc/asterisk/local
// wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install.php
// php install.php
//
// crontab -e add the following for time on the hr between 6am and 11pm
// 00 7-23 * * * php /etc/asterisk/local/weather_pws.php >> /dev/null
// or this for every hr
// 0 * * * * php /etc/asterisk/local/weather_pws.php >> /dev/null

$path="/etc/asterisk/local/mm-software";
include ("$path/config.php");
include ("$path/sound_db.php");
$file="$path/sound_gsm_db.csv";
$soundDbWav ="";
$soundDbGsm = file($file);
$soundDbUlaw="";


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
print"$datum Error loading node number";die;}
}
$testNewApi=true; // Future use. Still testing. This is for the NWS new system.

$cond        = "/tmp/conditions.gsm"  ;if(file_exists($cond)){unlink($cond);}
$condition   = "/tmp/condition.gsm"   ;if(file_exists($condition)){unlink($condition);}    
$currentTime = "/tmp/current-time.gsm";if(file_exists($currentTime)){unlink($currentTime);}
$vpath="/var/lib/asterisk/sounds";

$phpVersion= phpversion();
$ver= "v1.9";  
$time= date('H:i');
$date =  date('m-d-Y');
// Token generated for this script. owned by pws.winnfreenet.com
// You are authorised to use for this script only. 
$token = "473c0a7b78d24dc99c182f78619d0090";
//DO NOT COPY!!! Get your own
print "===================================================
";
print "mesowest, madis, APRSWXNET(CWOP) $ver
";
print "(c)2013/2023 WRXB288 LAGMRS.com all rights reserved
";
print "$phpzone PHP v$phpVersion NODE:$node
";
print "===================================================
";

// read the station
$apiString = "stid=$station&token=$token&units=english&output=xml";
$datum = date('[H:i:s]');
$gmdatum = gmdate('[H:i:s]');

$domain ="api.mesowest.net";
$datum   = date('m-d-Y H:i:s');
print "$datum Polling $station $domain >";
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $poll_start = $mtime;

$attime = gmdate('YmdHi');
$url = "/v2/stations/nearesttime?obtimezone=UTC&$apiString";
$html = file_get_contents("https://$domain/$url");
$file="/tmp/mesowest.xml";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$html);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$html = str_replace('"', "", $html);
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);
$poll_time = round($poll_time,2);
$MS_Error = strpos($html, 'error!'); if ($MS_Error){ $error=$html;}
if ($MS_Error){print "$error
";
watchdog ($error);
die;
}


$test = read_madis($html);

if (!$data_good){
$datum = date('[H:i:s]');
$gmdatum = gmdate('[H:i:s]');
print "*
$gmdatum $datum $error Aborted
";
watchdog ($error);
die;
}

print "<ok>$poll_time Sec. 
";
watchdog ("ok");
$the_temp=$outtemp;
$file="/tmp/temperature.txt";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$the_temp);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$datum   = date('m-d-Y H:i:s');$status="";
if($avgwind    >0){$status ="$status  Wind:$avgwind";}
if($rainofdaily>0){$status ="$status  Rain:$rainofdaily ";}
print "$datum $sitename  Temp:$the_temp  humidity:$outhumi% $status 
";


// 1 temp only 2=temp,cond 3= temp,cond,wind humi rain 
if ($level >1){
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);
$poll_time = round($poll_time,2);

// Poll acuweather for the current conditions. Ignore the temp
$file="/tmp/accuweather.xml";  $cond1="";$cond2="";$cond3="";
if(file_exists($file)){unlink($file);}

$datum   = date('m-d-Y H:i:s');
print "$datum Polling Accuweather $zipcode >";

$html = file_get_contents("http://rss.accuweather.com/rss/liveweather_rss.asp?metric=F&locCode=$zipcode");
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,"$html");flock ($fileOUT, LOCK_UN );fclose ($fileOUT);

$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos1 = strpos($line, 'Currently');
$pos2 = strpos($line, ':');
$len = strlen($line);
if (!$cond1){
 if ($pos1){
  $test = substr($line, $pos2+2,40);
  $pos2 = strpos($test, ':');
  $temp = substr($test, 0, $pos2);
  $datum   = date('m-d-Y H:i:s');
  $cond1=strtolower($temp);
   continue ;
}
}
if (!$cond2){
 if ($pos1){
  $test = substr($line, $pos2+2,40);
  $pos2 = strpos($test, ':');
  $temp = substr($test, 0, $pos2);
  $datum   = date('m-d-Y H:i:s');
  $cond2=strtolower($temp);
   continue ;
}
}
if (!$cond3){
 if ($pos1){
  $test = substr($line, $pos2+2,40);
  $pos2 = strpos($test, ':');
  $temp = substr($test, 0, $pos2);
  $datum   = date('m-d-Y H:i:s');
  $cond3=strtolower($temp);
   continue ;
}
}


} // end loop

$datum   = date('m-d-Y H:i:s');
print "<ok>$poll_time Sec. 
$datum conditions:  ($cond1,$cond2,$cond3)
";

$file="/tmp/conditions.txt";$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,"$the_temp F / $cond1 $cond2 $cond3");flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$vpath="/var/lib/asterisk/sounds";

// run a api test
if ($testNewApi){include("$path/forcast.php");}


} // end level 2
// ------------------------------------------------------------------------
$action="";
$hour = date('H');
$day  = date('l');
$hr =   date('h');
$min  = date('i');
//$oh=false;make_number ($hr);$theHR = $file1; $theHR2 = $file2;
//if ($min == 0 ){$theMin="$vpath/digits/oclock.gsm";$theMin2="";}
//else {$oh=true;make_number ($min);$theMin = $file1;$theMin2=$file2;}


$file=$currentTime; $cmd="";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'wb');fclose ($fileOUT);// create the file
//$fileIN = file_get_contents ($silence2);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $silence2";
check_gsm_db  ("silence2"); if($file1){$action = "$action $file1";}

$status ="";
if ($hour < 12 ) {$status = "good-morning"; }
if ($hour >= 12 and $hour <18) {$status = "good-afternoon";}
if ($hour >= 18) {$status = "good-evening"; }
//save_word ($status);
check_gsm_db  ($status); if($file1){$action = "$action $file1";}

$datum   = date('m-d-Y H:i:s');
check_gsm_db  ("the time is"); if($file1){$action = "$action $file1";}
//save_word ("the-time-is");
$oh=false;make_number ($hr);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
//if($theHR){$fileIN = file_get_contents ($theHR);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $theHR";}
//if($theHR2){$fileIN = file_get_contents ($theHR2);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $theHR2";}


if ($min == 0 ){
check_gsm_db ("oclock");if($file1){$action = "$action $file1";} 
}
else {$oh=true;make_number ($min);
   if($file1){$action = "$action $file1";}
   if($file2){$action = "$action $file2";}
}

//$theMin = $file1;$theMin2=$file2;
// if ($theMin != ""){$fileIN = file_get_contents ($theMin);file_put_contents($file,$fileIN, FILE_APPEND); $cmd="$cmd $theMin"; }
// if ($theMin2 != "") { $fileIN = file_get_contents ($theMin2);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $theMin2";}

if ($hour < 12 ){$pm="am";check_gsm_db ("a-m");if($file1){$action = "$action $file1";}} 
if ($hour >= 12){$pm="pm";check_gsm_db ("p-m");if($file1){$action = "$action $file1";}} 

print "$datum $status Time is $hr:$min $pm   
";
check_gsm_db ("silence2");if($file1){$action = "$action $file1";}
check_gsm_db ("weather");if($file1){$action = "$action $file1";}
// Weather
//save_word ("weather"); 
// 1 temp only 2=temp,cond 3= temp,cond,wind humi rain 


if ($level >1){
check_gsm_db ("conditions");if($file1){$action = "$action $file1";}   
$u = explode(" ",$cond1);
if ($cond1){check_gsm_db ($u[0]);if($file1){$action = "$action $file1";} 
 if (isset ($u[1])){check_gsm_db ($u[1]);if($file1){$action = "$action $file1";}} 
 }
if ($cond2){
$u = explode(" ",$cond2);
check_gsm_db ($u[0]);if($file1){$action = "$action $file1";}
 if (isset ($u[1])){check_gsm_db ($u[1]);if($file1){$action = "$action $file1";}} 
}
if ($cond3){
$u = explode(" ",$cond3);
check_gsm_db ($u[0]);if($file1){$action = "$action $file1";}
 if (isset ($u[1])){check_gsm_db ($u[1]);if($file1){$action = "$action $file1";}} 
}
//$datum   = date('m-d-Y H:i:s');
//print "$datum conditions:  ($cond1,$cond2,$cond3)
//";

if ($testNewApi){
if ($hour >= 18) { check_gsm_db ("tonight");if($file1){$action = "$action $file1";}}
else{check_gsm_db ("today");if($file1){$action = "$action $file1";}}


$shortForcast=strtolower($shortForcast); 

$shortForcast = str_replace('thunderstorms', "thunderstorm", $shortForcast);
$shortForcast = str_replace('chance', "chance-of", $shortForcast);
$shortForcast = str_replace('showers', "rain", $shortForcast);
$shortForcast = str_replace('nws', "national weather service", $shortForcast);
$shortForcast = str_replace('then', "later", $shortForcast);
 

$u = explode(" ",$shortForcast);
if ($shortForcast){
 check_gsm_db ($u[0]);if($file1){$action = "$action $file1";} 
 if (isset ($u[1])){check_gsm_db ($u[1]);if($file1){$action = "$action $file1";}} 
 if (isset ($u[2])){check_gsm_db ($u[2]);if($file1){$action = "$action $file1";}} 
 if (isset ($u[3])){check_gsm_db ($u[3]);if($file1){$action = "$action $file1";}} 
 if (isset ($u[4])){check_gsm_db ($u[4]);if($file1){$action = "$action $file1";}} 
 if (isset ($u[5])){check_gsm_db ($u[5]);if($file1){$action = "$action $file1";}} 
 if (isset ($u[6])){check_gsm_db ($u[6]);if($file1){$action = "$action $file1";}}    
 }

}


}

check_gsm_db ("temperature");if($file1){$action = "$action $file1";}
$oh=false;make_number ($the_temp);
if($file0){$action = "$action $file0";}
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
if($file3){$action = "$action $file3";}
check_gsm_db ("degrees");if($file1){$action = "$action $file1";} 

if($level>2){ // 1 temp only 2=temp,cond 3= temp,cond,wind humi rain 
check_gsm_db ("humidity");if($file1){$action = "$action $file1";}
$oh=false;make_number ($outhumi);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
check_gsm_db ("percent");if($file1){$action = "$action $file1";}

if($avgwind>1){
save_word ("wind"); 
$oh=false;make_number ($avgwind); 
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
check_gsm_db ("miles-per-hour");if($file1){$action = "$action $file1";} 
}

if ($rainofdaily>0){
check_gsm_db ("rain");if($file1){$action = "$action $file1";} 
list($whole, $decimal) = explode('.', $rainofdaily);
$oh=false;make_number ($whole);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
if($decimal>=1){
 check_gsm_db ("point");if($file1){$action = "$action $file1";} 
 $oh=false;make_number ($decimal);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
  }
 } 
} // end of level2

// Start the CPU temp warnings here.
$log="/tmp/cpu_temp_log.txt";
$datum = date('m-d-Y-H:i:s');
$line= exec("/opt/vc/bin/vcgencmd measure_temp",$output,$return_var);// SoC BCM2711 temp
$line = str_replace("'", "", $line);
$line = str_replace("C", "", $line);
$u= explode("=",$line);
$temp=$u[1];
$tempf = (float)(($temp * 9 / 5) + 32);
print "$datum $nodeName Temp is $tempf F $temp C
";

$line= exec("/opt/vc/bin/vcgencmd get_throttled",$output,$return_var);
//throttled=0x0
$u= explode("x",$line); 
$throttled = "";
if($u[1]== "0"){$throttled = "";}
if($u[1]== "1"){$throttled = "under-voltage-detected";}
if($u[1]== "2"){$throttled = "arm-frequency-capped";}
if($u[1]== "4"){$throttled = "currently-throttled";}
if($u[1]== "8"){$throttled = "soft-temp-limit-active";}
if($u[1]== "10000"){$throttled = "under-voltage-detected";}
if($u[1]== "20000"){$throttled = "arm-frequency-capping";}
if($u[1]== "80000"){$throttled = "throttling-has-occurred";}
if($u[1]== "80000"){$throttled = "soft-temp-limit-occurred";}


print "$datum $nodeName Throttled:$throttled code: $u[1] 
";
$fileOUT = fopen($log, "a") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$datum,$temp, \n");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

if ($reportAll or $temp >=$hot){
$vpath ="/var/lib/asterisk/sounds";
$cmd="";
check_gsm_db ($nodeName);if($file1){$action = "$action $file1";} 

list($whole, $decimal) = explode('.', $temp);
$oh=false;make_number ($whole);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
if($decimal>=1){
check_gsm_db ("point");if($file1){$action = "$action $file1";} 
$oh=false;make_number ($decimal);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
}
check_gsm_db ("degrees");if($file1){$action = "$action $file1";} 
check_gsm_db ("celsius");if($file1){$action = "$action $file1";} 

if ($temp >=$hot){
 if ($temp >=$high){check_gsm_db ("warning");if($file1){$action = "$action $file1";}} 
 else{check_gsm_db ("high");if($file1){$action = "$action $file1";}}
}
}


$datum   = date('m-d-Y H:i:s');
print "$datum Playing file $currentTime
";

exec ("sox $action $currentTime",$output,$return_var);

$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/current-time'",$output,$return_var);
if(!$status){$status="OK";}





if ($throttled){
$file="/tmp/throttled.ul";
$datum   = date('m-d-Y H:i:s');
print "$datum Playing file $file
";
check_name_cust ($throttled);
  if ($file1){ 
  $status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/throttled'",$output,$return_var);
  }
}
$datum   = date('m-d-Y H:i:s');
print "$datum finished  $status $return_var
";
print "===================================================
";

unset ($soundDbGsm);die;



function make_number ($in){
global $vpath,$file0,$file1,$file2,$file3,$negative,$oh;
// Speak all possible numbers
// PHP Number matrix

$file0 = "";$file1 = "";$file2 = "";$file3 = "";$negative="";
if ($in <0 ){$negative = "$vpath/digits/minus.gsm";}
$in = abs($in);
$in = round($in);
if ($oh){if ($in<10) {    $file1  = "$vpath/digits/oh.gsm";}}
if ($in >= 100){          $file3  = "$vpath/digits/hundred.gsm"; $in = ($in -100); }
if ($in>=20 and $in<30  ){$file1  = "$vpath/digits/20.gsm";$in=$in-20;} 
if ($in>=30 and $in<40  ){$file1  = "$vpath/digits/30.gsm";$in=$in-30;}
if ($in>=40 and $in<50  ){$file1  = "$vpath/digits/40.gsm";$in=$in-40;} 
if ($in>=50 and $in<60  ){$file1  = "$vpath/digits/50.gsm";$in=$in-50;}
if ($in>=60 and $in<70  ){$file1  = "$vpath/digits/60.gsm";$in=$in-60;} 
if ($in>=70 and $in<80  ){$file1  = "$vpath/digits/70.gsm";$in=$in-70;}
if ($in>=80 and $in<90  ){$file1  = "$vpath/digits/80.gsm";$in=$in-80;} 
if ($in>=90 and $in<100 ){$file1  = "$vpath/digits/90.gsm";$in=$in-90;}

if ($in >=1 and $in<20  ){$file2  = "$vpath/digits/$in.gsm";}           
}



function check_name_cust ($in){
global $file1,$path;
$customSound="$path/sounds";
$file1="";
if (file_exists("$customSound/$in.ul")){$file1 = "$customSound/$in";}
else{print"$customSound/$in.ul not found";}
}





// copyright by lagmrs.com  
// Source code may not be used in other programs pws.winnfreenet.com
// scrapper  v3 
function read_madis ($html) {
global $dateReading, $timeReading,$sitename,$data_good,$CurrTimeR,$CurrTimeMi,$CurrTimeHr,$CurrTime,$CurrTimeD,$CurrTimeM,$CurrTimeY,$html,$outtemp,$outhumi,$avgwind,$gustspeed,$rainofdaily,$rainofyearly;
$data_good = false;
$norain=true;
$ver ="MADIS";

$posname= strpos($html, '<NAME');

if ($posname) {
     $test = substr($html, ($posname),49); //print $test;
     $Lpos = strpos($test, '>');$Rpos = strpos($test, '</NAME');
     $sitename  = substr($test, $Lpos+1,$Rpos-$Lpos-1);
//     print $sitename;
     }

$posSTART= strpos($html, '<OBSERVATIONS type'); if ($posSTART){$data_good = true;}
$posEND  = strpos($html, '</OBSERVATIONS>');    if (!$posEND)  {$data_good = false;}
$posKey = ($posEND - $posSTART);


if ($posSTART){
 $html = substr($html, ($posSTART),$posKey);

//wind_speed_value_1
$Lpos = strpos($html, '<wind_speed_value_1');$Rpos = strpos($html, '</wind_speed_value_1>');
 if ($Lpos){
$test = substr($html, $Lpos,$Rpos);
$Lpos = strpos($test, '<value type');$Rpos = strpos($test, '</value>');
$avgwind  = substr($test, $Lpos+18,$Rpos-$Lpos-18);
 }
//<precip_accum_value_1 type=dict><date_time type=str>2017-12-05T16:52:00Z</date_time><value type=float>59.06</value></precip_accum_value_1>

//<precip_accum_since_local_midnight_value_1 type="dict"></precip_accum_since_local_midnight_value_1>

$Lpos = strpos($html, '<precip_accum_since_local_midnight_value_1');$Rpos = strpos($html, '</precip_accum_since_local_midnight_value_1');
if ($Lpos){
$test = substr($html, $Lpos,$Rpos);
$Lpos = strpos($test, '<value type');$Rpos = strpos($test, '</value>');
$rainofdaily  = substr($test, $Lpos+18,$Rpos-$Lpos-18);
$norain=false;
}

// air_temp_value_1
$Lpos = strpos($html, '<air_temp_value_1');$Rpos = strpos($html, '</air_temp_value_1>');
$test = substr($html, $Lpos,$Rpos-$Lpos+1);  $datecheck=$test;
// print "($test)$Lpos,$Rpos
$Lpos = strpos($test, 'float>');$Rpos = strpos($test, '</value');
$test = substr($test, $Lpos,$Rpos-$Lpos+1);
//print "($test)$Lpos,$Rpos
$Lpos = strpos($test, '>');$Rpos = strpos($test, '<');
$outtemp  = substr($test, $Lpos+1,$Rpos-$Lpos-1);
//print "$outtemp



// pull the date from the temp value
$test=$datecheck;
$posDate = strpos($test, '<date_time');

if ($posDate){
     $data_good = true;
     $test2 = substr($test, ($posDate),90);
     $Lpos = strpos($test2, '>');$Rpos = strpos($test2, '</date');
     $dateReading  = substr($test2, $Lpos+1,$Rpos-$Lpos-11);
     $timeReading  = substr($test2, $Lpos+12,$Rpos-$Lpos-13);
     $CurrTimeR = substr($timeReading, 0,8);// 15:00:00
     $CurrTimeD = substr($dateReading, 8,2); // 2017-08-03
     $CurrTimeY = substr($dateReading, 0,4); // 2017-08-03
     $CurrTimeM = substr($dateReading, 5,2);  // 2017-08-03
     $CurrTimeMi= substr($timeReading, 3,2);  // 15:00:00
     $CurrTimeHr= substr($timeReading, 0,2);  // 15:00:00
     $CurrTime  = $timeReading;
}

//relative_humidity_value_1
$Lpos = strpos($html, '<relative_humidity_value_1');$Rpos = strpos($html, '</relative_humidity_value_1>');
if ($Lpos){
$test = substr($html, $Lpos,$Rpos);
$Lpos = strpos($test, '<value type');$Rpos = strpos($test, '</value>');
$outhumi  = substr($test, $Lpos+18,$Rpos-$Lpos-18);
  }// hum
  
 }//start
}

function watchdog($in){
global $currentTime,$node;
// watch the internet
$file= "/tmp/watchdog.txt";$action="";
$counter = file_get_contents($file);
if ($in <>"ok"){$counter++;}
else {$counter=0;}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$counter);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);

if($counter >100){
$str = exec("ping -c 1 www.google.com");
if ($result == 0){return;}
else{
check_gsm_db ("an-error-has-occured");if($file1){$action = "$action $file1";}
check_gsm_db ("ping");if($file1){$action = "$action $file1";}
check_gsm_db ("connection-failed");if($file1){$action = "$action $file1";} 
// disconnected connection-failed an-error-has-occured
check_gsm_db ("error-number");if($file1){$action = "$action $file1";}
$oh=false;make_number ($counter);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
exec ("sox $action $currentTime",$output,$return_var);
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/current-time'",$output,$return_var);
if(!$status){$status="OK";}
// Normaly a reebot would go here. Planning
}
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
