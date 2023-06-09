<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
//    for allstar gmrs nodes to keep track of the temp
// -------------------------------------------------------------
//
//   _____ _____  _    _   _______                     __  __             _ _             
//  / ____|  __ \| |  | | |__   __|                   |  \/  |           (_) |            
// | |    | |__) | |  | |    | | ___ _ __ ___  _ __   | \  / | ___  _ __  _| |_ ___  _ __ 
// | |    |  ___/| |  | |    | |/ _ \ '_ ` _ \| '_ \  | |\/| |/ _ \| '_ \| | __/ _ \| '__|
// | |____| |    | |__| |    | |  __/ | | | | | |_) | | |  | | (_) | | | | | || (_) | |   
//  \_____|_|     \____/     |_|\___|_| |_| |_| .__/  |_|  |_|\___/|_| |_|_|\__\___/|_|   
//                                            | |                                         
//                                            |_| 
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
// This can be run from cron 
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

$path="/etc/asterisk/local/mm-software";
include ("$path/config.php");
include ("$path/sound_db.php");
$file="$path/sound_gsm_db.csv";
$soundDbWav ="";
$soundDbGsm = file($file);
$soundDbUlaw="";

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

$ver="v1.6";
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

$vpath ="/var/lib/asterisk/sounds";

$cpufile="/tmp/cpu.gsm";$file=$cpufile;
$cmd=""; 
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'wb');fclose ($fileOUT);// create the file
check_gsm_db ($nodeName);if($file1){$action = $file1;} 

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

$datum   = date('m-d-Y H:i:s');
print "$datum Playing file $cpufile 
";
exec ("sox $action $cpufile",$output,$return_var);
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/cpu'",$output,$return_var);
if(!$status){$status="OK";}

// These sounds are ul and can not be stacked into the gsm
// on a busy system the file may play before or overlay the above.


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


if(!$status){$status="OK";}


$datum = date('m-d-Y-H:i:s');
print "$datum finished  $status $return_var
===================================================
";
unset ($soundDbGsm);die;

// v1 modules 
//

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



function create_node ($file){
global $file,$path;
$line= exec("cat /usr/local/etc/allstar_node_info.conf  |egrep 'NODE1='",$output,$return_var);
$line = str_replace('"', "", $line);
$u= explode("=",$line);
$node=$u[1];
$file= "$path/mm-node.txt";
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node, , , , ");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);
}
