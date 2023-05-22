<?php
// (c)2021/2023 by The Master lagmrs.com    
// 
// pull temp from hubitat
//
//
//  you should already have maker api installed on the hub
// you will need a token the device no of the maker and the temp device
// consult maker api for token. No hub device driver is needed for this script
//
// This script replaces a perl and batch file in favor of PHP
//
//
// place in  /etc/asterisk/local
//
// The timezone in PHP is not setup properly in hamvoip
//  so you need to set your timezone.
//  // chrontab -e add the following for time on the hr between 6am and 11pm
// 00 7-23 * * * php /etc/asterisk/local/weather_hubitat.php >> /tmp/time.txt
define('TIMEZONE', 'America/Chicago');
date_default_timezone_set(TIMEZONE);
$zone =	ltrim(exec('timedatectl | grep "Time zone"'));//testing get timezone from hub
$node="2955"; // Your node number
$level = 3 ;// 1 temp only 2=temp,cond 3= temp,cond,wind humi rain 
$phpVersion= phpversion();
$ver= "v1.0";  
$time= date('H:i');
$date =  date('m-d-Y');
//$WBdatum = gmdate('m-d-Y [H:i:s]');
$datum   = date('m-d-Y H:i:s');

// HUB SETTINGS
$hub="192.168.0.13";// Hubitat
$maker="282"; // Device # for the maker API
$token = "0ba48ca1-f585-41b7-bfd4-2f9b7a134ed5";// access token from maker API
$device ="662";  // device no for the temp sensor wet=rain
$zipcode="71432";$fc = "f";// accuweather.com forcast zipcode


$sitename = "Hubitat";
$time_zone = date_default_timezone_get();

print " =============================================
";
print " Hubitat local temp $ver 
";
print " PHP Time zone:$time_zone   PHP:$phpVersion
";
print " PI $zone
";
print " =============================================
";
// poll the hubs sensors

$datum   = date('m-d-Y H:i:s');
print "$datum Polling HUB:$hub
";
$pos1="";$temp="";$cond1="";$cond2="";$cond3="";
$html = file_get_contents("http://$hub/apps/api/$maker/devices/$device?access_token=$token"); 
$pos1 = strpos($html, 'temperature');
if ($pos1){$test = substr($html, ($pos1),50);$Lpos = strpos($test, 'currentValue');$Rpos = strpos($test, 'dataType');$the_temp= substr($test, $Lpos+14,$Rpos-$Lpos-16);}
$pos1 = strpos($html, 'wet');$wet="";
if ($pos1){$test = substr($html, ($pos1),50);$Lpos = strpos($test, 'currentValue');$Rpos = strpos($test, 'dataType');$wet= substr($test, $Lpos+14,$Rpos-$Lpos-16);}
$file="/tmp/hubatat.xml";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$html);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);



$file="/tmp/temperature.txt";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$the_temp);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$datum   = date('m-d-Y H:i:s');
print "$datum $sitename  Temp:$the_temp  
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

?>
