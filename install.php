<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
//   This installs the main scripts 
// weather_pws.php
// temp.php and /sounds/
// check_gmrs.sh   modified ping file for gmrs live
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

$ver="v1.0";
$out="";
// This install is very fast only the last lines are readable
print "===================================================
";

chdir("/etc/asterisk/local/");

install($out);

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
$path  = "/etc/asterisk/local";
$path2 = "$path/sounds";
$repo="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/sounds/";
$u = explode(",",$files);
chdir($path);
if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2);
$datum = date('m-d-Y-H:i:s');
print"
$datum Install sounds
";

foreach($u as $file) {
if (!file_exists("$path2/$file")){ 
   $d= exec("sudo wget $repo/$file ",$output,$return_var);
   }
   }
// install other
$files = "temp.php,weather_pws.php,check_gmrs.sh";
$repo="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/";
$error="";
chdir($path);
$datum = date('m-d-Y-H:i:s');
print"
$datum Installing scripts
";
foreach($u as $file) {
if (!file_exists("$path/$file")){ 
   $d= exec("sudo wget $repo/$file ",$output,$return_var);
   }
   }
}
