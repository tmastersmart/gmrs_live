<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
// This installer sets up and installs the scripts. You still need
// to edit the script and install to cron yourself.
//
// Later versions might auto install to cron.
// 
// weather_pws.php
// temp.php and /sounds/
//
//
// check_gmrs.sh   modified for gmrs live
//
// -------------------------------------------------------------

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

$ver="v1.2";
$out="";
// This install is very fast only the last lines are readable
print "===================================================
";
$path= "/etc/asterisk/local/mm-software";

install($out);
// automatic node setup
$file= "$path/mm-node.txt";
create_node ($file);




print "
===================================================
Custom installer $ver Finished 
(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
$phpzone PHP v$phpVersion
===================================================

This installer will not update yet. You need to check for updates manualy

type 
nano temp.php
and add your node number

type
nano weather_pws.php
and set your local weather station and node#.
see instructions in file.

Thank you for downloading........... And have Many nice days
";




function install($in){

$files = "hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";
$path  = "/etc/asterisk/local/mm-software";// moved to special dir for dist.
$path2 = "$path/sounds";

$u = explode(",",$files);
if(!is_dir($path)){ mkdir($path, 0755);}
chdir($path);
if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2);
$repo="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/sounds";
$datum = date('m-d-Y-H:i:s');
print"
$datum Install sounds
";

foreach($u as $file) {
if (!file_exists("$path2/$file")){
   print "sudo wget $repo/$file
   "; 
exec("sudo wget $repo/$file",$output,$return_var);
   }
   }
// install other
$files = "config.php,temp.php,weather_pws.php,check_gmrs.sh";
$repo2="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$error="";
chdir($path);
$datum = date('m-d-Y-H:i:s');
print"
$datum Installing scripts
";
$u = explode(",",$files);
foreach($u as $file) {
if (!file_exists("$path/$file")){ 
   print "sudo wget $repo2/$file
   "; 
 exec("sudo wget $repo2/$file ",$output,$return_var);
   }
   }
}

function create_node ($file){
global $file,$path;
// phase 1 import node
$line= exec("cat /usr/local/etc/allstar_node_info.conf  |egrep 'NODE1='",$output,$return_var);
$line = str_replace('"', "", $line);
$u= explode("=",$line);
$node=$u[1];
$file= "$path/mm-node.txt";
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node, , , , ");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);


// phase 2 import skywarn settings 
$file="/usr/local/bin/AUTOSKY/AutoSky.ini";
$file2="$path/skywarn.txt";
$fileOUT = fopen($file2, "w") ;
$line = exec("cat /usr/local/bin/AUTOSKY/AutoSky.ini  |egrep 'OFILE='",$output,$return_var);
$line = str_replace('"', "", $line);
$u= explode("=",$line);
fwrite ($fileOUT,"$u[0],$u[1]=$u[2]=$u[3]\r");
$line = exec("cat /usr/local/bin/AUTOSKY/AutoSky.ini  |egrep 'Coverage_Area='",$output,$return_var);
$line = str_replace('"', "", $line);
$u= explode("=",$line);
fwrite ($fileOUT,"$u[0],$u[1]\r");
flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
}
