<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
//    for allstar gmrs nodes to keep track of the temp
// -------------------------------------------------------------
//
// The node image is missing the CPU temp monitor at
// /sys/class/thermal/thermal_zone0/temp
//
// I am using the GPU temp monitor at
// /opt/vc/bin/vcgencmd measure_temp
// Supermon uses the same monitor
//
//
//Pi must stay below 85c at all times. To be safe 70 is my danger zone. 
//The PI will regulate its own temp by throttling down when its hot, 
//but throttling down reduces cpu cycles and will likely cause dropouts. 
// 
//
// install
//
// place in  /etc/asterisk/local
// wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install.php
// php install.php
//
// This can be run from chron 
// Note duplicate version ofthis script is built into the weather script  
//
//
//crontab -e add the following for time on the hr between 6am and 11pm
// at 30 mins on the hr to prevent overrun with time.
// 30 7-23 * * * php /etc/asterisk/local/temp.php >> /dev/null

//
//https://www.raspberrypi.com/documentation/computers/processors.html
//
//
//
//NOTICE NOTICE on First run the script will download sound files from..
//https://github.com/tmastersmart/gmrs_live/tree/main/sounds
//This process is very fast and might alarm you if you dont expect it.
//
// NOTES:
//
//If you need it there is a script that will turn the fan on and off with the temp.
//This is a old script thats no longer used on the new PIs since its now built in the OS.
//https://howchoo.com/g/ote2mjkzzta/control-raspberry-pi-fan-temperature-python
//Do not run the autoinstall script. Its not compatable with the node image
//Only install the script here.
//https://github.com/Howchoo/pi-fan-controller/blob/master/fancontrol.py 
//and run it at load time.  /etc/rc.local
//
$reportAll = true; // change to false to not talk for normal temp
$nodeName = "server";// What name do you want it to use
//$nodeName = "system";// must be a file that exists in "/var/lib/asterisk/sounds"
//$nodeName = "node";
$high = 60;// 85 is danger
$hot  = 50;
// 45 is normal 50 is slightly hot. I recomend cooling at 50-60. I would not run anything above 70
// see https://raspberrypi.stackexchange.com/questions/114462/how-much-temperature-is-normal-temperature-for-raspberry-pi-4


// PHP is in UTC Get in sync with the PI
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

$path="/etc/asterisk/local/mm-software";
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

$ver="v1.5";
$out="";
print "===================================================
";
print " PI temp Monitor $ver Node:$node
"; 
print "(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
";
print "$phpzone PHP v$phpVersion
";
print "===================================================
";
chdir($path);


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


print "$datum $nodeName Status: $throttled code: $u[1] 
";

$fileOUT = fopen($log, "a") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$datum,$temp, \n");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

if (!$reportAll and $temp <=$hot){die;}

$speak = "/tmp/temp.gsm";
$vpath ="/var/lib/asterisk/sounds";
$file=$speak; $cmd="";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'wb');fclose ($fileOUT);// create the file
check_name ($nodeName); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
list($whole, $decimal) = explode('.', $temp);
$oh=false;make_number ($whole);
if (file_exists($file1)){  $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
if (file_exists($file2)){  $fileIN = file_get_contents ($file2);file_put_contents ($file,$fileIN, FILE_APPEND);}
if($decimal>=1){
check_name ("point"); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
$oh=false;make_number ($decimal);
if (file_exists($file1)){  $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
if (file_exists($file2)){  $fileIN = file_get_contents ($file2);file_put_contents ($file,$fileIN, FILE_APPEND);}
}

check_name ("degrees"); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
check_name ("celsius"); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}



$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/temp '",$output,$return_var);
if(!$status){$status="OK";}
 $datum   = date('m-d-Y H:i:s');
print "$datum Playing file $speak
";

// These sounds are ul and can not be stacked into the gsm
// on a busy system the file may play before or overlay the above.

if ($temp >=$hot){
 if ($temp >=$high){$status="$status >$high WARNING";check_name_cust ("warning");}
 else{$status="$status >$hot HOT";check_name_cust ("hot");} 
  if ($file1){ 
  $status= exec("sudo asterisk -rx 'rpt localplay $node $file1'",$output,$return_var);
  $datum   = date('m-d-Y H:i:s');
print "$datum Playing file $file1
";
  }
}
if ($throttled){
check_name_cust ($throttled);$status="$status $throttled";
  if ($file1){ 
  $status= exec("sudo asterisk -rx 'rpt localplay $node $file1'",$output,$return_var);
  $datum   = date('m-d-Y H:i:s');
print "$datum Playing file $file1
";
  }
}


if(!$status){$status="OK";}


$datum = date('m-d-Y-H:i:s');
print "$datum finished  $status $return_var
===================================================
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
global $vpath,$file1,$file;
$file1="";
$fileSound= "$vpath/$in.gsm";
if (file_exists($fileSound)){$file1 = "$fileSound/$in";}
  }

function check_name_cust ($in){
global $file1,$path;
$path="$path/sounds/";
$file1="";
if (file_exists("$path/$in.ul")){$file1 = "$path/$in";}
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
