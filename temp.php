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
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_start = $mtime;

$path="/etc/asterisk/local/mm-software";
include ("$path/load.php");
include ("$path/sound_db.php");



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



$datum = date('m-d-Y-H:i:s');
$ver="v1.9";
$out="";
print "
===================================================
 PI temp Monitor  $coreVersion-t$ver 
(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
$phpzone PHP v$phpVersion
===================================================
$datum Model: $piVersion
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


if($throttled){
$status ="$nodeName $throttled code:$u[1]";save_task_log ($status);
print "$datum $status 
";
}

$fileOUT = fopen($log, "a") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$datum,$temp, \n");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

if (!$reportAll and $temp <=$hot){line_end("NORMAL Temp.");}
$cmd=""; $action="";
$cpufile="/tmp/cpu.gsm";$file=$cpufile;if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'wb');fclose ($fileOUT);// create the file

check_wav_db("star dull");if($file1){$action = "$action $file1";}

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
check_wav_db("star dull");if($file1){$action = "$action $file1";}
check_gsm_db ("silence2");if($file1){$action = "$action $file1";}

$datum   = date('m-d-Y H:i:s');
print "$datum Playing file $cpufile 
";





exec ("sox $action $cpufile",$output,$return_var);
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/cpu'",$output,$return_var);

line_end("Finished");






?>
