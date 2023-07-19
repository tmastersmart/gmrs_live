#!/usr/bin/php
<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
// This installer sets up and installs the software. 
//
// all software is hand coded in php from scratch 
// in North Louisiana.
//
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
// v2.6 07/15/023  New download and update routines
//                 New bridging detection
//                 Node directory for Repeaters and hubs
//                 Supermon updates. 
//                 New update process with merge files for supermon. So I wont be redistributiong anything


// stage 3
//                 Plans for supermon. 
//                 1 click connect from the new directory
//                 Log book for 
//                 Call sign look up


$phpVersion= phpversion();
$path= "/etc/asterisk/local/mm-software";
$ver="v2.9"; $release="07-18-2023";
$out="";
print "
   _____ __  __ _____   _____   _           _        _ _           
  / ____|  \/  |  __ \ / ____| (_)         | |      | | |          
 | |  __| \  / | |__) | (___    _ _ __  ___| |_ __ _| | | ___ _ __ 
 | | |_ | |\/| |  _  / \___ \  | | '_ \/ __| __/ _` | | |/ _ \ '__|
 | |__| | |  | | | \ \ ____) | | | | | \__ \ || (_| | | |  __/ |   
  \_____|_|  |_|_|  \_\_____/  |_|_| |_|___/\__\__,_|_|_|\___|_| 

PHP:$phpVersion  Installer:$ver  Release date:$release
(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
============================================================
 Welcome to my php installer.                                                  
                                                          
 This will install and setup the node controler software.                                              

 Most everything is automated no more editing config files.
                                                         
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
============================================================
PHP:$phpVersion  Installer:$ver  Release date:$release
(c) 2023 by WRXB288 LAGMRS.com all rights reserved
Custom installer Finished 
============================================================

Software Made in loUiSiAna
Thank you for downloading........... And have Many nice days

Software was installed to $path
to manualu load setup type
cd $path
php setup.php

>>>>>>>>>>>>>>>>>Autoloading setup.php <<<<<<<<<<<<<
";
include ("$path/setup.php");
}
else {print "
Aborted  Type 'php install.php' to try again
";}


function installa($in){

$repo = "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$path1 = "/srv/http/supermon";
$path  = "/etc/asterisk/local/mm-software"; if(!is_dir($path)){ mkdir($path, 0755);}
$path2 = "$path/sounds";if(!is_dir($path2)){ mkdir($path2, 0755);}
$path3 = "$path/repo";if(!is_dir($path3)){ mkdir($path3, 0755);}
$path4 = "$path/backup";if(!is_dir($path4)){ mkdir($path4, 0755);}
 chdir($path3);

clean_($path3);

  print "Downloading the repo from the archive \n";
  
  exec("sudo wget $repo/core-download.zip",$output,$return_var);
  exec("sudo wget $repo/sounds.zip",$output,$return_var);
  exec("sudo wget $repo/supermon.zip",$output,$return_var);  
  exec("unzip core-download.zip",$output,$return_var);

$files = "setup.php,supermon_weather.php,load.php,forcast.php,temp.php,cap_warn.php,weather_pws.php,sound_db.php,check_reg.php,nodelist_process.php";
$u = explode(",",$files);
foreach($u as $file) {
  print "Installing -PHP $file\n";
  if (file_exists("$path/$file")){unlink("$path/$file");}
  rename ("$path3/$file", "$path/$file");
  exec("sudo chmod +x $path/$file",$output,$return_var); 
 }  
$files = "sound_gsm_db.csv,sound_wav_db.csv,sound_ulaw_db.csv,check_gmrs.sh,cron.txt";
$u = explode(",",$files);
foreach($u as $file) {
  print "Installing -database $file\n";
  if (file_exists("$path/$file")){unlink("$path/$file");}
  rename ("$path3/$file", "$path/$file");
 }  
  exec("unzip sounds.zip",$output,$return_var);
  
$files = "bridged.gsm,clear.wav,heat_advisory.wav,flood_advisory.wav,weather_service.wav,hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";
$u = explode(",",$files);
foreach($u as $file) {
  print "Installing -Sound $file\n";
  if (file_exists("$path2/$file")){unlink("$path2/$file");}
  rename ("$path3/$file", "$path2/$file");
} 

exec("unzip supermon.zip",$output,$return_var);


chdir($path1); 
$fileBu = "$path1/links.php.bak"; 
if (file_exists($fileBu) and file_exists("$path3/link.merge")){
unlink ("link.php");
copy ($fileBu,"$path1/links.php");   // Bring in org file so we can merge
exec("patch -u -b /srv/http/supermon/link.php -i $path3/link.merge",$output,$return_var); // merge in changes...
}

else{
 if (!file_exists($fileBu)){print"Unknown error $fileBu does not exist.\n";}
 if (!file_exists("$path3/link.merge")){print"Unknown error $path3/link.merge does not exist.\n";}
}


$files = "gmrs-rep.php,gmrs-hubs.php,gmrs-list.php";
$u = explode(",",$files);
foreach($u as $file) {
  print "Installing -Supermon mods  $file\n";
  if (file_exists("$path1/$file")){unlink("$path1/$file");}
  rename ("$path3/$file", "$path1/$file");
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
 
$file="/usr/local/bin/AUTOSKY/SOUNDS/asn02.wav";
if(!file_exists($file)){ 
print"
We need the sound files from AUTOSKY for cap_warn 
but they are missing.
for legal reasions I do not redistrubute them.
They will now be installed by packman. enter Y
";  
 exec("pacman -Sy hamvoip-autosky",$output,$return_var);
}
} 
function clean_($in){

   chdir($in);
   
   foreach (glob("*.zip") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
 foreach (glob("*.php") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
 foreach (glob("*.txt") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
 foreach (glob("*.csv") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
 foreach (glob("*.ul") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    }
 foreach (glob("*.wav") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    }
 foreach (glob("*.gsm") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    }  
    
  foreach (glob("*.merge") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    }     

} 
 
