<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
//
// -------------------------------------------------------------
//
// The node image is missing the CPU temp monitor at
// /sys/class/thermal/thermal_zone0/temp
//
// I am using the GPU temp monitor at
// /opt/vc/bin/vcgencmd measure_temp
// Supermon also uses this the CPU monitor
//
//
//Pi must stay below 85c at all times. To be safe 80 is my danger zone. 
//The Pi will start throttling to reduce heat. Reducing cpu cycles could 
//problems in audio.  You need to keep it under about 60c.
//You should have a heatsync. Note If room air is arround 80c
//then it cant control its heat and you are in danger. 
//
//
// place in  /etc/asterisk/local
// wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/temp.php
// sound files will autodownload on first run type
// php temp.php
//
// This can be run from chron or it will be called if your are running 
// my weather_pws.php file from chron.
//
//
//chrontab -e add the following for time on the hr between 6am and 11pm
// at 30 mins on the hr to prevent overrun with time.
// 30 7-23 * * * php /etc/asterisk/local/temp.php >> /tmp/temp.txt

//
//https://www.raspberrypi.com/documentation/computers/processors.html
//
//
//
//NOTICE NOTICE on First run the script will download sound files from..
//https://github.com/tmastersmart/gmrs_live/tree/main/sounds
//
//
//This process is very fast and might alarm you if you dont expect it.
//
$node="2955";// Set your node number

$reportAll = true;
//$nodeName = "server";// What name do you want it to use
//$nodeName = "system";// must be a file that exists in "/var/lib/asterisk/sounds"
$nodeName = "node";
$high = 80;// hot 85 is danger
$hot  = 60;// still ok
$warn = 50;
$normal = 45;
define('TIMEZONE', 'America/Chicago');
$ver="v1.0";
$out="";
print "===================================================
";
print " PI temp Monitor $ver 
"; 
print "(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
";
print "===================================================
";

if (!file_exists("/etc/asterisk/local/sounds/warning.ul")){ install($out);}
chdir("/etc/asterisk/local/");

$log="/tmp/cpu_temp_log.txt";
$datum = date('m-d-Y-H:i:s');
$line= exec("/opt/vc/bin/vcgencmd measure_temp",$output,$return_var);// SoC BCM2711 temp
$line = str_replace("'", "", $line);
$line = str_replace("C", "", $line);
$u= explode("=",$line);
$temp=$u[1];
print "$datum $nodeName Temp is $temp C
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


print "$datum $nodeName Status: $throttled code: $u[1] 
";





$fileOUT = fopen($log, "a") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$datum,$temp, \n");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);
if (!$reportAll and $temp <=$warn){die;}
$speak = "/tmp/temp.gsm";
$vpath ="/var/lib/asterisk/sounds";
$file=$speak; $cmd="";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'wb');flock ($fileOUT, LOCK_EX );
check_name ($nodeName); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
$oh=false;make_number ($temp);
if (file_exists($file1)){  $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
if (file_exists($file2)){  $fileIN = file_get_contents ($file2);file_put_contents ($file,$fileIN, FILE_APPEND);}
check_name ("degrees"); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
check_name ("celsius"); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$datum   = date('m-d-Y H:i:s');
print "$datum Playing file to NODE:$node $speak
";
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/temp '",$output,$return_var);
if(!$status){$status="OK";}


if ($temp >=$high){
$status="$status WARNING";
check_name_cust ("warning");
if($file1){
$d= exec("sudo asterisk -rx 'rpt localplay $node $file1 '",$output,$return_var);
$status="$status $d WARN";
sleep(1);
}
}
if ($throttled){
$status="$status $throttled";
check_name_cust ($throttled);
if($file1){
$d= exec("sudo asterisk -rx 'rpt localplay $node $file1 '",$output,$return_var);
$status="$status $d";
sleep(1);
}
 }
$datum = date('m-d-Y-H:i:s');
print "$datum finished  $status $return_var
"; 
print "===================================================
";



// v1 modules 
//

function make_number ($in){
global $file0,$file1,$file2,$file3,$negative,$oh;
// Speak all possible numbers
// PHP Number matrix
$path ="/var/lib/asterisk/sounds";
$file0 = "";$file1 = "";$file2 = "";$file3 = "";$negative="";
if ($in <0 ){$negative = "$path/digits/minus.gsm";}
$in = abs($in);
$in = round($in);
if ($oh){if ($in<10) {    $file1  = "$path/digits/oh.gsm";}}
if ($in >= 100){          $file3  = "$path/digits/hundred.gsm"; $in = ($in -100); }
if ($in>=20 and $in<30  ){$file1  = "$path/digits/20.gsm";$in=$in-20;} 
if ($in>=30 and $in<40  ){$file1  = "$path/digits/30.gsm";$in=$in-30;}
if ($in>=40 and $in<50  ){$file1  = "$path/digits/40.gsm";$in=$in-40;} 
if ($in>=50 and $in<60  ){$file1  = "$path/digits/50.gsm";$in=$in-50;}
if ($in>=60 and $in<70  ){$file1  = "$path/digits/60.gsm";$in=$in-60;} 
if ($in>=70 and $in<80  ){$file1  = "$path/digits/70.gsm";$in=$in-70;}
if ($in>=80 and $in<90  ){$file1  = "$path/digits/80.gsm";$in=$in-80;} 
if ($in>=90 and $in<100 ){$file1  = "$path/digits/90.gsm";$in=$in-90;}
if ($in >=1 and $in<20  ){$file2  = "$path/digits/$in.gsm";}           
}

function check_name ($in){
global $file1;
$path ="/var/lib/asterisk/sounds";
$file1="";
$fileSound= "$path/$in.gsm"; if (file_exists($fileSound)){$file1 = $fileSound;}
}


function check_name_cust ($in){
global $file1;
$path="/etc/asterisk/local/sounds/";
$file1="";
if (file_exists("$path/$in.ul")){$file1 = "$path/$in";}
}

function install($in){

$files = "warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";
$path  = "/etc/asterisk/local";
$path2 = "$path/sounds";
$repo="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/sounds/";
$u = explode(",",$files);
chdir($path);
if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2);
$datum = date('m-d-Y-H:i:s');
print"$datum Starting one time install from github
";

foreach($u as $file) {
if (!file_exists("$path2/$file")){ 
   $d= exec("sudo wget $repo/$file ",$output,$return_var);
   }
   }
print "
";
}
