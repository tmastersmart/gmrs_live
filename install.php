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


$phpVersion= phpversion();
$path= "/etc/asterisk/local/mm-software";
$ver="v1.5";
$out="";
print "
   _____ __  __ _____   _____   _           _        _ _           
  / ____|  \/  |  __ \ / ____| (_)         | |      | | |          
 | |  __| \  / | |__) | (___    _ _ __  ___| |_ __ _| | | ___ _ __ 
 | | |_ | |\/| |  _  / \___ \  | | '_ \/ __| __/ _` | | |/ _ \ '__|
 | |__| | |  | | | \ \ ____) | | | | | \__ \ || (_| | | |  __/ |   
  \_____|_|  |_|_|  \_\_____/  |_|_| |_|___/\__\__,_|_|_|\___|_| 

PHP:$phpVersion  Installer:$ver
============================================================
= Welcome                                                  =
=                                                          =
= This installer will install all the php programs and     =
= sound files.                                             =
=                                                          =
============================================================
Software will be installed to $path

 i) install
 Any other key to abort 
";
$a = readline('Enter your command: ');

if ($a=="i"){


installa($out);
// automatic node setup
$file= "$path/mm-node.txt";
create_nodea ($file);






print "
===================================================
Custom installer $ver Finished 
(c) 2023 by WRXB288 LAGMRS.com all rights reserved 

===================================================

Software Made in loUiSiAna


Thank you for downloading........... And have Many nice days

Software was installed to $path

type

cd $path

php setup.php

";
include ("$path/setup.php");
}
else {print "
Aborted  Type 'php install.php' to try again
";}

function installa($in){

$files = "clear.wav,flood_advisory.wav,weather_service.wav,hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";
$path  = "/etc/asterisk/local/mm-software";
$path2 = "$path/sounds";

$u = explode(",",$files);
if(!is_dir($path)){ mkdir($path, 0755);}
chdir($path);
if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2);
$repo="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/sounds";
$datum = date('m-d-Y-H:i:s');
print"$datum Install sounds
";

foreach($u as $file) {
if (!file_exists("$path2/$file")){
   print "sudo wget $repo/$file
   "; 
exec("sudo wget $repo/$file",$output,$return_var);
   }
   }
// install other
$files = "supermon.txt,config.php,setup.php,forcast.php,temp.php,skywarn.php,weather_pws.php,sound_db.php,check_gmrs.sh,sound_db.php,sound_wav_db.csv,sound_gsm_db.csv";


$repo2="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$error="";
chdir($path);
$datum = date('m-d-Y-H:i:s');
print"$datum Installing scripts
";
$u = explode(",",$files);
foreach($u as $file) {
if (!file_exists("$path/$file")){ 
   print "sudo wget $repo2/$file
   "; 
 exec("sudo wget $repo2/$file ",$output,$return_var);
   }
   }
  exec("sudo chmod +x *.php",$output,$return_var);  
}

function create_nodea ($file){
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
$file2="$path/autosky_import.ini";
copy($file, $file2);

}
