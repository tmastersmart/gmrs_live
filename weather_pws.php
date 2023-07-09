  #!/usr/bin/php
<?php
// (c)2015/2023 by The Master lagmrs.com  by pws.winnfreenet.com
// This script uses some code my weather programs. Member CWOP since 2015 

// I license you to use this on your copy of this software only.
// Not for use on anything but GMRS.  GMRS ONLY! 

// No part of this code is opensource 
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
// wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install.php
// php install.php
//


$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_start = $mtime;

$path="/etc/asterisk/local/mm-software";
include ("$path/load.php");
include ("$path/sound_db.php");
include ("$path/check_reg.php");
$ver= "v3.0";  
$muteTime1= 1; $muteTime2=6;//  MUTE SOUND from 2-5 am 
// Get php timezone in sync with the PI
$line =	exec('timedatectl | grep "Time zone"'); //       Time zone: America/Chicago (CDT, -0500)
$line = str_replace(" ", "", $line);
$pos1 = strpos($line, ':');$pos2 = strpos($line, '(');
if ($pos1){  $zone   = substr($line, $pos1+1, $pos2-$pos1-1); }
else {$zone="America/Chicago";}
define('TIMEZONE', $zone);
date_default_timezone_set(TIMEZONE);
$phpzone = date_default_timezone_get(); // test it 
if ($phpzone == $zone ){$phpzone=$phpzone;}
else{$phpzone="$phpzone ERROR";}

$currentTime = "/tmp/current-time.gsm";if(file_exists($currentTime)){unlink($currentTime);}
$clash="/tmp/mmweather-task.txt";
$error="";$action="";
$phpVersion= phpversion();

$time= date('H:i');
$date =  date('m-d-Y');
// Token generated for this script. owned by LAGMRS.com
// I license you to use this on your copy of this software only.
// Not for use on anything but GMRS.  GMRS ONLY! 
$token = "473c0a7b78d24dc99c182f78619d0090";
$datum   = date('m-d-Y H:i:s');$gmdatum = gmdate('m-d-Y H:i:s');
print "
===================================================
mesowest, madis, APRSWXNET(CWOP) $coreVersion-w$ver
(c)2013/2023 WRXB288 LAGMRS.com all rights reserved
$phpzone PHP v$phpVersion
===================================================
$datum Model: $piVersion
$datum Node:$node UTC:$gmdatum Level:$level
";

// test for clash (only 1 thread allowed)
if(file_exists($clash)){unlink($clash);
 $out="Program thread already running Aborting";
 save_task_log($out);line_end($out);
}

$fileOUT = fopen($clash,'w');fwrite ($fileOUT,$datum);fclose ($fileOUT);

// will be part of custom GMRS_Supermon 
if($beta){ include ("$path/nodelist_process.php"); sort_nodes ("nodes"); }

// read the station
$apiString = "stid=$station&token=$token&units=english&output=xml";
$datum = date('[H:i:s]');
$gmdatum = gmdate('[H:i:s]');

$domain ="api.mesowest.net";
$datum   = date('m-d-Y H:i:s');
print "$datum Polling $station $domain >";
$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0];$poll_start = $mtime;

$attime = gmdate('YmdHi');
$url = "/v2/stations/nearesttime?obtimezone=UTC&$apiString";

$options = array(
    'http'=>array(
        'timeout' => 30,  
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
                  "Cookie: foo=bar\r\n" .
                  "User-Agent: Allstar Node lagmrs.com \r\n" 
));
$context = stream_context_create($options);
$html = @file_get_contents("https://$domain/$url");
$file="/tmp/mesowest.xml";
if(file_exists($file)){unlink($file);}


$out = str_replace("</", "
</", $html); // make easer to debug
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,"https://$domain/$url $out");flock ($fileOUT, LOCK_UN );fclose ($fileOUT);

$html = str_replace('"', "", $html);
$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0];$poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);$poll_time = round($poll_time,2);

$test = read_madis($html);

$datum = date('[H:i:s]');

if ($data_good){ // test for stale data
$month = date('m');$year = date('Y');$hour = date('H');$day = date('d');$min = gmdate('i');$timeH= gmdate('H');$time= gmdate('Hi');$dayUTC = gmdate('d');
$testMin = $min - $CurrTimeMi;
if ($testMin < 0) {$testMin = $testMin+60;}
//if ($testMin > $min){print "($testMin Mins Old) $timeReading";$data_good = false;}
if ($CurrTimeD < $day ){print "Wrong day. $CurrTimeD < $day :$dateReading";$data_good=false;}
if ($CurrTimeY <> $year){print "Wrong year. $dateReading";$data_good=false;}
if ($testMin > 40){print "($testMin Mins Old) $timeReading";$data_good = false; }
if($data_good){
$validTemp = true;
print "<ok> UTC:$CurrTime $poll_time Sec. 
";
$action = watchdog ("oknet");
//$the_temp=$outtemp;
$the_temp=number_format($outtemp, 2);
//$file="/tmp/temperature.txt";if(file_exists($file)){unlink($file);}
//$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$the_temp);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$datum   = date('m-d-Y H:i:s');$status="$sitename Temp:$the_temp";
if($heatIndex){$status ="$status HeatIndex:$heatIndex";}
if($WindChill){$status ="$status WindChill:$WindChill";}
if($outhumi){$status ="$status hum:$outhumi%";}
if($avgwind    >0){$status ="$status Wind:$avgwind";}
if($rainofdaily>0){$status ="$status Rain:$rainofdaily";}
$condWX=$status;save_task_log ($condWX);//print "$datum $condWX";
 } // good data
}


else{ 
$datum   = date('m-d-Y H:i:s');
$status="<error>";if($poll_time>=10){$status="$status timeout";}
save_task_log ("MesoWest error $status $poll_time Sec.");
print "$datum $status $poll_time Sec.
";
$action = watchdog ("net");
$validTemp = false;
$condWX=""; $the_temp="";
} // bad data

// END WX



// 1 temp 2=cond 3=wind humi rain 4=forcast
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

$datum   = date('m-d-Y H:i:s');print "$datum Polling Accuweather $zipcode >";

$options = array(
    'http'=>array(
        'timeout' => 10,  
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
                  "Cookie: foo=bar\r\n" .
                  "User-Agent: Allstar Node lagmrs.com \r\n" 
));
$context = stream_context_create($options);

$html = @file_get_contents("http://rss.accuweather.com/rss/liveweather_rss.asp?metric=F&locCode=$zipcode");

if($html){
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
}
}

}
$datum   = date('m-d-Y H:i:s');
print "<ok>$poll_time Sec. 
";
watchdog ("oknet");
} // end if html
else { 
$datum   = date('m-d-Y H:i:s');
$status="<error>";if($poll_time>=10){$status="$status timeout";}
save_task_log ("Acuweather error $url $status $poll_time Sec.");
print "$datum $status $poll_time Sec.
";
$level = 1;// Drop down level due to error}
watchdog ("net");
} // end loop


$file="/tmp/conditions.txt";$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,"$condWX $cond1 $cond2 $cond3");flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$vpath="/var/lib/asterisk/sounds";

// run the forcast
//if ($forcast){  
include("$path/forcast.php");// mandatory to get alerts
if ($event="clear"){$event="";} // Skip if clear

if ($shortForcast ){$out ="$cond1 | $shortForcast";}
else {$out=$cond1;}
save_task_log ($out);
} // end level 2


// ------------------------------------------------------------------------
$hour = date('H');$day  = date('l');$hr =   date('h');$min  = date('i');
$cmd="";

check_wav_db("star dull");if($file1){$action = "$action $file1";} 
//check_gsm_db  ("silence2"); if($file1){$action = "$action $file1";}
//good,good-afternoon,good-evening,good-morning, (need good night)
$status ="";
if ($hour < 12)                {check_gsm_db("good-morning");  if($file1){$action="$action $file1";}}
if ($hour >=12 and $hour <=18) {check_gsm_db("good-afternoon");if($file1){$action="$action $file1";}}
if ($hour >=19)                {check_gsm_db("good-evening");  if($file1){$action="$action $file1";}}



$datum   = date('m-d-Y H:i:s');
check_gsm_db  ("the time is"); if($file1){$action = "$action $file1";}
//save_word ("the-time-is");
$oh=false;make_number ($hr);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}


//if ($min == 0 ){check_gsm_db ("oclock");if($file1){$action = "$action $file1";}}  // this just doesnt sound right with am/pm after it

if ($min>=1){
$oh=true;make_number ($min);
   if($file1){$action = "$action $file1";}
   if($file2){$action = "$action $file2";}
}

if ($hour < 12 ){$pm="am";check_gsm_db ("a-m");if($file1){$action = "$action $file1";}} 
if ($hour >= 12){$pm="pm";check_gsm_db ("p-m");if($file1){$action = "$action $file1";}} 

$datum   = date('m-d-Y H:i:s');
print "$datum The time is $hr:$min $pm   
";
check_gsm_db ("silence2");if($file1){$action = "$action $file1";}





// 1 temp 2=cond 3=wind humi rain 4=forcast 
if ($level >1){
$datum   = date('m-d-Y H:i:s');
print "$datum Conditions: $cond1
";

check_gsm_db ("weather"); if($file1){$action = "$action $file1";}
check_gsm_db ("conditions");if($file1){$action = "$action $file1";}   

$u = explode(" ",$cond1);
foreach ($u as $word) {
 if($word){check_gsm_db ($word);if($file1){$action = "$action $file1";} }
}
} // level 1 end


// -----------------------forcast --------------------
// 1 temp 2=cond 3=wind humi rain 4=forcast
if ($level >=4){
 if ($forcast){
  if ($shortForcast) {
$status = "today";
if ($hour >= 12 and $hour <19) {$status = "evening";}  
if ($hour >= 19 and $hour <21) {$status = "tonight";} 

check_gsm_db ($status);if($file1){$action = "$action $file1";}
$test = strtolower($shortForcast); 
$test = str_replace('thunderstorms', "thunderstorm", $test);
$test = str_replace('chance', "chance-of", $test);
$test = str_replace('showers', "rain", $test);
$test = str_replace('nws', "national weather service", $test);
$test = str_replace('then', "later", $test);
$test = str_replace('slight', "low", $test);
$u = explode(" ",$test);
foreach ($u as $word) {
 if($word){check_gsm_db ($word);if($file1){$action = "$action $file1";} }
    }
   }
  } // forcast end
 } // level 4 end

 
 
 
 
 
 

if ($level>=2){
// Events are in a diffrent database
if ($event){
//print "DEBUG $event";
check_gsm_db ("alert");if($file1){$action = "$action $file1";} 
check_wav_db ("light click"); if($file1){$action = "$action $file1";} 
//$event = str_replace(",", " ", $event);
$u = explode(",",$event);
foreach ($u as $line) {
//$word = strtolower($word);
if($line){ check_wav_db($line);if($file1){$action = "$action $file1";} } // star dull
 
// check for major warrnings.
// persons in path of 
if($line=="Tornado Warning"){
check_gsm_db ("persons in path of");if($file1){$action = "$action $file1";}
check_gsm_db ("tornado");if($file1){$action = "$action $file1";}
check_gsm_db ("advised to seek shelter");if($file1){$action = "$action $file1";}
}

if($line=="Severe Thunderstorm Warning"){
check_gsm_db ("persons in path of");if($file1){$action = "$action $file1";}
check_gsm_db ("thunderstorm");if($file1){$action = "$action $file1";}
check_gsm_db ("advised to seek shelter");if($file1){$action = "$action $file1";}
}

if($line=="Hurricane Warning"){
check_gsm_db ("persons in path of");if($file1){$action = "$action $file1";}
check_gsm_db ("hurricane");if($file1){$action = "$action $file1";}
check_gsm_db ("advised to seek shelter");if($file1){$action = "$action $file1";}
}
if($line=="Blizzard Warning"){
check_gsm_db ("persons in path of");if($file1){$action = "$action $file1";}
check_gsm_db ("blizzard");if($file1){$action = "$action $file1";}
check_gsm_db ("advised to seek shelter");if($file1){$action = "$action $file1";}
}


}
//check_gsm_db ("wow");if($file1){$action = "$action $file1"; }
//check_gsm_db ("silence1");if($file1){$action = "$action $file1";}
}
}
 



if($the_temp) {
$datum   = date('m-d-Y H:i:s');
print "$datum $condWX
"; 
check_gsm_db ("temperature");if($file1){$action = "$action $file1";}

list($whole, $decimal) = explode('.', $the_temp);
$oh=false;make_number ($whole);
if($file0){$action = "$action $file0";}
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
if($file3){$action = "$action $file3";}
if($decimal>=1){
 check_gsm_db ("point");if($file1){$action = "$action $file1";} 
 $oh=true;make_number ($decimal);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
}
check_gsm_db ("degrees");if($file1){$action = "$action $file1";} 
// make a comment on the temp
if ($the_temp >90 or $the_temp <20){check_gsm_db ("moo1");if($file1){$action = "$action $file1";}} 
}

//$heatIndex
$test=$the_temp+15;
if($heatIndex >$test) {
check_gsm_db ("heat index");if($file1){$action = "$action $file1";}
list($whole, $decimal) = explode('.', $heatIndex);
$oh=false;make_number ($whole);
if($file0){$action = "$action $file0";}
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
if($file3){$action = "$action $file3";}
//if($decimal>=1){
// check_gsm_db ("point");if($file1){$action = "$action $file1";} 
// $oh=false;make_number ($decimal);
//if($file1){$action = "$action $file1";}
//if($file2){$action = "$action $file2";}
//}
check_gsm_db ("degrees");if($file1){$action = "$action $file1";} 
// make a comment on the temp
if ($heatIndex >90 ){check_gsm_db ("moo1");if($file1){$action = "$action $file1";}} 
}



if($WindChill) {
check_gsm_db ("wind chill");if($file1){$action = "$action $file1";}
list($whole, $decimal) = explode('.', $WindChill);
$oh=false;make_number ($whole);
if($file0){$action = "$action $file0";}
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
if($file3){$action = "$action $file3";}
//if($decimal>=1){
// check_gsm_db ("point");if($file1){$action = "$action $file1";} 
// $oh=false;make_number ($decimal);
//if($file1){$action = "$action $file1";}
//if($file2){$action = "$action $file2";}
//}
check_gsm_db ("degrees");if($file1){$action = "$action $file1";} 
// make a comment on the temp
if ($WindChill <20 ){check_gsm_db ("moo1");if($file1){$action = "$action $file1";}} 
}


if($level>2){ // 1 temp only 2=temp,cond 3= temp,cond,wind humi rain 
 if($outhumi){
check_gsm_db ("humidity");if($file1){$action = "$action $file1";}
$oh=false;make_number ($outhumi);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
check_gsm_db ("percent");if($file1){$action = "$action $file1";}
}

if($avgwind>0){
check_gsm_db ("wind");if($file1){$action = "$action $file1";} 

list($whole, $decimal) = explode('.', $avgwind);
$oh=false;make_number ($whole);
if($file0){$action = "$action $file0";}
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
if($file3){$action = "$action $file3";}
if($decimal>=1){
 check_gsm_db ("point");if($file1){$action = "$action $file1";} 
$oh=false;make_number ($decimal);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
}
check_gsm_db ("miles-per-hour");if($file1){$action = "$action $file1";} 
}

if ($rainofdaily>0){
check_gsm_db ("rainfall");if($file1){$action = "$action $file1";} 
list($whole, $decimal) = explode('.', $rainofdaily);
$oh=false;make_number ($whole);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
if($decimal>=1){
 check_gsm_db ("point");if($file1){$action = "$action $file1";} 
 $oh=true;make_number ($decimal);
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
  }
 check_gsm_db ("inches");if($file1){$action = "$action $file1";}  
 } 
} // end of level2

// Start the CPU temp warnings here.
$log="/tmp/cpu_temp_log.txt";
$datum = date('m-d-Y H:i:s');
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

if($throttled){
$status ="$nodeName $throttled code:$u[1]";save_task_log ($status);print "$datum $status
";

}
$fileOUT = fopen($log, "a") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$datum,$temp, \n");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

if ($reportAll or $temp >=$hot){
//$vpath ="/var/lib/asterisk/sounds";
$cmd="";
check_gsm_db ("silence1");if($file1){$action = "$action $file1";}
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
   $status ="CPU HOT $temp";save_task_log ($status);
 if ($temp >=$high){check_gsm_db ("warning");if($file1){$action = "$action $file1";}} 
 else{check_gsm_db ("high");if($file1){$action = "$action $file1";}}
}
if ($throttled){check_ulaw_db ($throttled);if($file1){$action = "$action $file1";}  }
} // end temp


// Check the reg
reg_check ("check");// $node1 $ip $port2 $registered
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

//  "Auth. Sent"  "Registered"    rejected: 'Registration Refused' // Request,Auth.,
$pos1 = strpos("-$registered", 'Unregistered');if($pos1){check_gsm_db ("is-not-registered");if($file1){$action = "$action $file1";}}
$pos1 = strpos("-$registered", 'Refused');     if($pos1){check_gsm_db ("is-rejected");      if($file1){$action = "$action $file1";}}
$pos1 = strpos("-$registered", 'Auth');        if($pos1){check_gsm_db ("connecting");       if($file1){$action = "$action $file1";}}
$pos1 = strpos("-$registered", 'Request');     if($pos1){check_gsm_db ("not-yet-connected");if($file1){$action = "$action $file1";}}
}
if ($registered =="Registered"){watchdog ("okreg");}

// testing say node registered.
$test=false;
if ($test and $registered =="Registered"){
check_gsm_db ("node");if($file1){$action = "$action $file1";} 
$oh=false;
 $x = (string)$node1;
 for($i=0;$i<strlen($x);$i++)
 { 
make_number ($x[$i]); 
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
}

check_gsm_db ("is-registered");if($file1){$action = "$action $file1";} 
}



 
// ---------------------------------------------------play the file---------------------
$datum   = date('m-d-Y H:i:s'); $hour = date('H');

if($sleep){
 if($hour>$MuteTime1 and $hour <$muteTime2){
$out="Night time Muted $hour"; save_task_log ($out);
print "$datum $out
";}
}
else{
print "$datum Playing file $currentTime
";
check_gsm_db ("silence1");if($file1){$action = "$action $file1";}
check_wav_db("star dull");if($file1){$action = "$action $file1";}

exec("sox $action $currentTime",$output,$return_var);//print "DEBUG $action";
exec("sudo asterisk -rx 'rpt localplay $node /tmp/current-time'",$output,$return_var);

}




// check the working port -- Put it back to default on next reboot
if (!$NotReg){
$port = find_port ("find");
if ($port != "4569"){
$out="Port $port in use"; save_task_log ($out);print "$datum $out
";
 $newPort=4569;
 rotate_port("rotate");
 }
}

if($counter >$watchdog and $NotReg){
reg_fix ("check");
if(!$NotReg){
$out="We are back online"; // Yep I fixed it.
save_task_log ($out);print "$datum $out
"; 
 }
}
if(file_exists($clash)){unlink($clash);}
line_end("Finished");

//---------------------------------------------------------------------------------------------





// copyright by lagmrs.com  
// Source code may not be used in other programs pws.winnfreenet.com
// scrapper  v3 
function read_madis ($html) {
global $WindChill,$heatIndex, $dateReading, $timeReading,$sitename,$data_good,$CurrTimeR,$CurrTimeMi,$CurrTimeHr,$CurrTime,$CurrTimeD,$CurrTimeM,$CurrTimeY,$html,$outtemp,$outhumi,$avgwind,$gustspeed,$rainofdaily,$rainofyearly;
$data_good = false;
$norain=true; $WindChill="";$heatIndex="";
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





// Heat Index
$Lpos = strpos($html, '<heat_index_value_1d');$Rpos = strpos($html, '</heat_index_value_1d>');
$test = substr($html, $Lpos,$Rpos-$Lpos+1);  $datecheck=$test;
// print "($test)$Lpos,$Rpos
$Lpos = strpos($test, 'float>');$Rpos = strpos($test, '</value');
$test = substr($test, $Lpos,$Rpos-$Lpos+1);
//print "($test)$Lpos,$Rpos
$Lpos = strpos($test, '>');$Rpos = strpos($test, '<');
$heatIndex  = substr($test, $Lpos+1,$Rpos-$Lpos-1);



// wind_chill
$Lpos = strpos($html, '<wind_chill_value_1d');$Rpos = strpos($html, '</wind_chill_value_1d'); // </wind_chill_value_1d></wind_chill>
$test = substr($html, $Lpos,$Rpos-$Lpos+1);  $datecheck=$test;
// print "($test)$Lpos,$Rpos
$Lpos = strpos($test, 'float>');$Rpos = strpos($test, '</value');
$test = substr($test, $Lpos,$Rpos-$Lpos+1);
//print "($test)$Lpos,$Rpos
$Lpos = strpos($test, '>');$Rpos = strpos($test, '<');
$WindChill  = substr($test, $Lpos+1,$Rpos-$Lpos-1);




//relative_humidity_value_1
$Lpos = strpos($html, '<relative_humidity_value_1');$Rpos = strpos($html, '</relative_humidity_value_1>');
if ($Lpos){
$test = substr($html, $Lpos,$Rpos);
$Lpos = strpos($test, '<value type');$Rpos = strpos($test, '</value>');
$outhumi  = substr($test, $Lpos+18,$Rpos-$Lpos-18);
  }// hum
  
 }//start
}







?>
