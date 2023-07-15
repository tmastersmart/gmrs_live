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

// v1.6 06/01/2023 This is the first release with a mostly automated setup and installer.
// v1.7 06/02/2023 Debugging after moving to seperate subdirectory. 
// v1.8 06/03/2023 
// v2.0 06/09/2023 new databases . Rewrite of sound file system.
// v2.3 06/13/2023 Major finished release with setup and installer 
// v2.4 06/21/2023 many add ons reg fix new api alerts decoding
// v2.5 07/05/2023 
//
// stage 1
// v2.0 06/29/2023 new core released  with seperate module versions#s
//                 Automated Reg down detection and automated fix
//                 Many changes to alerts,Alerts now play with time,Reg down notification is in cap_warn and weather_pws               
//                 Many changes to setup program. Auto install of super mon is a work in progress and wont be released until fully tested.


// stage 2
//                 First stages of a GMRS directory are working see the nodelist being created each day.
//                 The future plan is to create my own supermon front end once stage 1 is perfected. 

// stage 3
//                 much much more..............................


$phpVersion= phpversion();
$path= "/etc/asterisk/local/mm-software";
$ver="v2.5";
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
// his only installs what does not exist. So you can rerun it over and over in case of error
// ,advisory.gsm
//
//  https://github.com/tmastersmart/gmrs_live/zipball/master/  get the zip 
//  exec("sudo wget $repo/$file",$output,$return_var);

//
$files = "bridged.gsm,clear.wav,heat_advisory.wav,flood_advisory.wav,weather_service.wav,hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";
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
$files = "setup.php,supermon_weather.php,load.php,forcast.php,temp.php,cap_warn.php,weather_pws.php,sound_db.php,check_reg.php,nodelist_process.php,check_gmrs.sh,sound_db.php";

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
 exec("sudo chmod +x $file",$output,$return_var); 
   }
   }
   
// non chmod files to install
$files = "supermon.txt,readme.txt,sound_wav_db.csv,sound_gsm_db.csv,sound_ulaw_db.csv";
$u = explode(",",$files);
foreach($u as $file) {
if (!file_exists("$path/$file")){ 
 print "sudo wget $repo2/$file
   "; 
 exec("sudo wget $repo2/$file ",$output,$return_var);
 }   
 }  
 
// Install the supermon mods
chdir("/srv/http/supermon");
$files = "links.php,gmrs-rep.php,gmrs-hubs.php,gmrs-list.php,";
$u = explode(",",$files);
foreach($u as $file) {
print "sudo wget $repo2/supermon/$file";
//exec("sudo wget $repo2/$file ",$output,$return_var);
} 
  
 
 
 
   
}

function create_nodea ($file){
global $file,$path,$tts;
// phase 1 import node - call
//$line= exec("cat /usr/local/etc/allstar_node_info.conf  |egrep 'NODE1='",$output,$return_var);
$file ="/usr/local/etc/allstar_node_info.conf"; copy($file, "$path/allstar_node_info.conf");
$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$line = str_replace('"', "", $line);
$pos = strpos("-$line", 'NODE1=');
if ($pos){$u= explode("=",$line);
$node=$u[1];}
$pos2 = strpos("-$line", 'STNCALL='); 
if ($pos2){$u= explode("=",$line);
$call=$u[1];}
}



// /usr/local/etc/tts.conf 
// Get any tss key if exists
$file ="/usr/local/etc/tts.conf";
$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$line = str_replace('"', "", $line);
$pos = strpos("-$line", 'tts_key=');
if ($pos){$u= explode("=",$line);
$tts=$u[1];}
}

$file= "$path/mm-node.txt";// This will be the AutoNode varable
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node,$call,$tts, , ,");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

// phase 2 build the admin menu
$file ="/usr/local/sbin/firsttime/adm01-shell.sh";
$file2="/usr/local/sbin/firsttime/mmsoftware.sh";
copy($file, $file2);// copy existing to get correct permissions
$file= $file2;
$out='#/!bin/bash
#MENUFT%055%Time Temp Weather Alert Setup/ MM Software Setup

$SON
reset

php /etc/asterisk/local/mm-software/setup.php

exit 0
';
// overwrite with our menu.
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, $out);flock( $fileOUT, LOCK_UN );fclose ($fileOUT);
 exec("sudo chmod +x $file",$output,$return_var);
 
$file="/usr/local/bin/AUTOSKY/SOUNDS/asn02";
if(!file_exists($file)){ 
 print" We need the sound files from AUTOSKY so installing them.\n";  
 exec("pacman -Sy hamvoip-autosky",$output,$return_var);
}
 
 
 
}
