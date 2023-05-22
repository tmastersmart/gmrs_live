<?php
// (c)2015/2023 by The Master lagmrs.com  by pws.winnfreenet.com
// This script uses some code my weather programs. Member CWOP since 2015 
// Licensed only for GMRS,Allstar & Hamvoip nodes. All rights reserved. 
// 
// pull temp from mesowest, madis, APRSWXNET/Citizen Weather Observer Program (CWOP)
// For persional Weather Stations and Airports
//
// http://www.wxqa.com/  (CWOP) main website
// https://mesowest.utah.edu/
// https://madis-data.ncep.noaa.gov
// https://aprs.fi/
// 
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
// The timezone in PHP is not setup properly in hamvoip
//  so you need to set your timezone.

// find your local MADIS station and airport go to the map https://madis-data.ncep.noaa.gov/MadisSurface/
// make sure all DATASETS are turned on and find the code your your station and your closest airport
//
// Contact me if you have a local station running AmbientWeather ObserverIP and wish to submit weather data to CWOP.
// I am testing a new PHP version that will run on a PI..... pws.winnfreenet.com 
//
// chrontab -e add the following for time on the hr between 6am and 11pm
// 00 7-23 * * * php /etc/asterisk/local/weather_pws.php >> /tmp/time.txt

$node="2955";
$station="E6758";// this is your local Station ID (CWOP)  EXXXX Starts with a E or a callsign (see map)
$fc="F";$zipcode="71432";// acuweather will say cloudy or such
define('TIMEZONE', 'America/Chicago');
$level = 3 ;// 1 temp only 2=temp,cond 3= temp,cond,wind humi rain 

date_default_timezone_set(TIMEZONE);
$zone =	exec('timedatectl | grep "Time zone"'); //testing
$phpVersion= phpversion();
$ver= "v1.0";  
$time= date('H:i');
$date =  date('m-d-Y');
// Token generated for this script. owned by pws.winnfreenet.com
// You are authorised to use for this script only. 
$token = "473c0a7b78d24dc99c182f78619d0090";
//DO NOT COPY!!! Get your own
print " =============================================
";
print " mesowest, madis, APRSWXNET(CWOP) script
";
print " (c) 2013/2023 by winnfreenet.com
";
print " =============================================
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
die;
}


$test = read_madis($html);

if (!$data_good){
$datum = date('[H:i:s]');
$gmdatum = gmdate('[H:i:s]');
print "*
$gmdatum $datum $error Aborted
";
die;
}

print "<ok>$poll_time Sec. 
";
$the_temp=$outtemp;
$file="/tmp/temperature.txt";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$the_temp);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$datum   = date('m-d-Y H:i:s');
print "$datum $sitename  Temp:$the_temp  humidity:$outhumi% Rain:$rainofdaily Wind:$avgwind 
";



if ($level >1){
// Poll acuweather for the current conditions. Ignore the temp its not local
// http://rss.accuweather.com/rss/liveweather_rss.asp\?metric\=${FAHRENHEIT}\&locCode\=$1
$file="/tmp/accuweather.xml";  $cond1="";$cond2="";$cond3="";
if(file_exists($file)){unlink($file);}
$html = file_get_contents("http://rss.accuweather.com/rss/liveweather_rss.asp?metric=$fc&locCode=$zipcode");
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





$file="/tmp/conditions.txt";$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,"$the_temp F / $cond1 $cond2 $cond3");flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$vpath="/var/lib/asterisk/sounds";
$cond="/tmp/conditions.gsm";$file=$cond;
if(file_exists($file)){unlink($file);} // Mostly Cloudy
$fileOUT = fopen($file,'wb');flock ($fileOUT, LOCK_EX );  $cmd="";

$u = explode(" ","$cond1 ");
if ($cond1){
check_name ($u[0]);
check_name ($u[1]); 
check_name ($u[2]);
}
if ($cond2){
$u = explode(" ",$cond2);
check_name ($u[0]); 
check_name ($u[1]); 
}
if ($cond3){
$u = explode(" ",$cond3);
check_name ($u[0]); 
check_name ($u[1]); 
}
flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
// End of condictions ==============================================================================================



$datum   = date('m-d-Y H:i:s');
print "$datum conditions:  ($cond1 $cond2 $cond3)
";
} // end level 2
// end conditions
$hour = date('H');
$day  = date('l');
$hr =   date('h');
$min  = date('i');



$oh=false;make_number ($hr);$theHR = $file1; $theHR2 = $file2;

if ($min == 0 ){$theMin="$vpath/digits/oclock.gsm";$theMin2="";}
else {$oh=true;make_number ($min);$theMin = $file1;$theMin2=$file2;}

$datum   = date('m-d-Y H:i:s');
print "$datum Local Time $hr:$min  
";



$silence1    = "$vpath/silence/1.gsm";
$silence2    = "$vpath/silence/2.gsm";
$condition   = "/tmp/condition.gsm";    
$currentTime = "/tmp/current-time.gsm";
$file=$currentTime; $cmd="";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'wb');flock ($fileOUT, LOCK_EX );
$fileIN = file_get_contents ($silence1);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $silence2";

$preFile="";$am=false;$status ="good night";
if ($hour < 12 ) {$status = "good-morning";  $am =true; check_name ($status);}
if ($hour >= 12) {$status = "good-afternoon";$am =false;check_name ($status);}
if ($hour >= 18) {$status = "good-evening";  $am =false;check_name ($status);}

check_name ("the-time-is");
$oh=false;make_number ($hr);$theHR = $file1; $theHR2 = $file2;
if($theHR){$fileIN = file_get_contents ($theHR);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $theHR";}
if($theHR2){$fileIN = file_get_contents ($theHR2);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $theHR2";}


if ($min == 0 ){check_name ("oclock");$theMin="";$theMin2="";}
else {$oh=true;make_number ($min);$theMin = $file1;$theMin2=$file2;
 if ($theMin != ""){$fileIN = file_get_contents ($theMin);file_put_contents($file,$fileIN, FILE_APPEND); $cmd="$cmd $theMin"; }
 if ($theMin2 != "") { $fileIN = file_get_contents ($theMin2);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $theMin2";}
}
if ($am=true){$fileIN = file_get_contents ("$vpath/digits/a-m.gsm");file_put_contents ($file,$fileIN, FILE_APPEND);}
else{$fileIN = file_get_contents ("$vpath/digits/p-m.gsm");file_put_contents ($file,$fileIN, FILE_APPEND);}

$fileIN = file_get_contents ($silence2);file_put_contents ($file,$fileIN, FILE_APPEND);
// Weather
check_name ("weather");
check_name ("conditions");
$fileIN = file_get_contents ($cond);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $cond";
check_name ("temperature");

$oh=false;make_number ($the_temp);
if($file0){$fileIN = file_get_contents ($file0);file_put_contents($file,$fileIN, FILE_APPEND);}
if($file1){$fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
if($file2){$fileIN = file_get_contents ($file2);file_put_contents ($file,$fileIN, FILE_APPEND);}
if($file3){$fileIN = file_get_contents ($file3);file_put_contents ($file,$fileIN, FILE_APPEND);}
check_name ("degrees");

if($level>2){
check_name ("humidity");
$oh=false;make_number ($outhumi);
if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
if ($file2){ $fileIN = file_get_contents ($file2);file_put_contents ($file,$fileIN, FILE_APPEND);}
check_name ("percent");

if($avgwind>1){
check_name ("wind"); 
$oh=false;make_number ($avgwind);if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $degrees";}
check_name ("miles-per-hour");
}

if ($rainofdaily>1){
 check_name ("rain"); 
 $oh=false;make_number ($rainofdaily);if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);$cmd="$cmd $rainofdaily";}
}
} 
 
flock ($fileOUT, LOCK_UN );fclose ($fileOUT);

$datum   = date('m-d-Y H:i:s');
print "$datum Playing file to NODE:$node $currentTime
";
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/current-time'",$output,$return_var);
if(!$status){$status="OK";}
print "$datum finished  $status $return_var
";
print " =============================================
";        




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

function check_name ($in){
global $vpath,$file1,$file;
$file1="";
$fileSound= "$vpath/$in.gsm";
if (file_exists($fileSound)){
  $fileIN = file_get_contents ($fileSound);file_put_contents ($file,$fileIN, FILE_APPEND);
  }
}




// copyright by winnfreenet.com  
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
}


}
}
?>
