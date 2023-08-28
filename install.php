#!/usr/bin/php
<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
// This installer sets up and installs the software. 
// all software is hand coded in php from scratch 
// in North Louisiana.
// -------------------------------------------------------------

// v1.6 06/01/2023 This is the first release with a mostly automated setup and installer.
// v1.7 06/02/2023 Debugging after moving to seperate subdirectory. 
// v1.8 06/03/2023 
// v2.0 06/09/2023 new databases . Rewrite of sound file system.
// v2.3 06/13/2023 Major finished release with setup and installer 
// v2.4 06/21/2023 many add ons reg fix new api alerts decoding
// v2.5 07/05/2023 
// v2.0 06/29/2023 new core released  with seperate module versions#s
//                 Automated Reg down detection and automated fix
//                 Many changes to alerts,Alerts now play with time,Reg down notification is in cap_warn and weather_pws               
//                 Many changes to setup program. Auto install of super mon is a work in progress and wont be released until fully tested.
//                 First stages of a GMRS directory are working see the nodelist being created each day.
// v2.6 07/15/023  New download and update routines  New bridging detection   Node directory for Repeaters and hubs
// v3.3 07/28/2023 Lots of new addons. New connect sounds. 
// v3.4
// v3.8 08/08/2023 new supermon lsnodes add on. bug fixes
// v4.1 08/12/2023 
// v4.3 08/28/2023 Rewrite of gmrs supermon now gets installed in a seperate directory. Totaly diffrent install code.
//                 Many many changes New GMRS supermon is not yet finished and changes are being made daily
//                 New installer

$phpVersion= phpversion();
$path= "/etc/asterisk/local/mm-software";
$ver="v4.3"; $release="08-28-2023";
$out="";
c641($in);
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

 All modules are optional you dont have to use the time and temp
 or you can use time and temp and not supermon.
============================================================
Software will be installed to $path

 i) install
 Any other key to abort 
";
$a = readline('Enter your command: ');

if ($a=="i"){
installa($out);

print " [checking install ....";
// automatic node setup
$file= "$path/mm-node.txt";create_nodea ($file); print"-";
$path= "/etc/asterisk/local/mm-software"; chdir($path);   print"-";


if (file_exists("$path/setup.php")){include ("$path/setup.php");}
else {print "Error install failed! $path/setup.php missing\n";}

print "
Software Made in loUiSiAna
Thank you for downloading........... And have Many nice days
Software was installed to $path

cd $path
php setup.php
";
}
else {
print "
Aborted  Type 'php install.php' to try again\n";}


function installa($in){

// Dual code to be in setup_install.php and install.php

$path  = "/etc/asterisk/local/mm-software";        
$repoURL= "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$pathS = "$path/sounds";if(!is_dir($pathS)){ mkdir($pathS, 0755);}
$pathR = "$path/repo";  if(!is_dir($pathR)){ mkdir($pathR, 0755);}
$pathB = "$path/backup";if(!is_dir($pathB)){ mkdir($pathB, 0755);}
$pathG = "/srv/http/gmrs";if(!is_dir($pathG)){ mkdir($pathG, 0755);}
$pathGA= "/srv/http/gmrs/admin";if(!is_dir($pathGA)){ mkdir($pathGA, 0755);}
$pathGE= "/srv/http/gmrs/edit";if(!is_dir($pathGE)){ mkdir($pathGE, 0755);}
$pathI = "/srv/http/gmrs/images";if(!is_dir($pathI)){ mkdir($pathI, 0755);}
$pathN = "/var/lib/asterisk/sounds/rpt/nodenames";
 
print"Cleaning any existing repos......\n";
chdir($pathR);

          
$file = "$pathR/core-download.zip"; if (file_exists($file)){unlink ($file);}
$file = "$repoR/sounds.zip"; if (file_exists($file)){unlink ($file);}
$file = "$repoR/supermon.zip";if (file_exists($file)){unlink ($file);}
$file = "$repoR/nodenames.zip";if (file_exists($file)){unlink ($file);}
$file = "$repoR/gmrs.zip"; if (file_exists($file)){unlink ($file);}
$file = "$repoR/admin.zip";if (file_exists($file)){unlink ($file);}
$file = "$repoR/images.zip"; if (file_exists($file)){unlink ($file);}

chdir($pathR);

 print "Downloading new repos ...........\n";
  exec("sudo wget $repoURL/core-download.zip",$output,$return_var);
  exec("sudo wget $repoURL/sounds.zip",$output,$return_var);
//exec("sudo wget $repo/supermon.zip",$output,$return_var);
  exec("sudo wget $repoURL/nodenames.zip",$output,$return_var); 
  exec("sudo wget $repoURL/gmrs.zip",$output,$return_var);
  exec("sudo wget $repoURL/admin.zip",$output,$return_var);
  exec("sudo wget $repoURL/images.zip",$output,$return_var);

 print "Downloading finished...........\n";
 chdir($pathR);
  
 exec("unzip $pathR/core-download.zip",$output,$return_var);
  
     
   foreach (glob("*.php") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing php file:$path/$file "; 
    if (file_exists("$path/$file")){unlink("$path/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){
    rename ("$pathR/$file", "$path/$file");
     exec("sudo chmod +x $path/$file",$output,$return_var); 
    } 
    print"ok\n";
    }
  }
  
   foreach (glob("*.csv") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing csv file:$path/$file "; 
    if (file_exists("$path/$file")){unlink("$path/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$path/$file"); } 
    print"ok\n";
    }
  }  
 
   foreach (glob("*.txt") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing txt file:$path/$file "; 
    if (file_exists("$path/$file")){unlink("$path/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$path/$file"); } 
    print"ok\n";
    }
  }  
 
 

if (file_exists("$path/taglines.txt")){
exec("touch -d 19910101 $path/taglines.txt",$output,$return_var);// Just being funny taglines are very old.
}




exec("unzip $pathR/sounds.zip",$output,$return_var);
//$path2 = "$path/sounds";$path3 = "$path/repo";$path4 = "$path/backup"; // just for debugging
chdir($pathR);   
 foreach (glob("*.wav") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing wav file:$pathS/$file "; 
    if (file_exists("$pathS/$file")){unlink("$pathS/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathS/$file");} // Move it into the SOUNDS
    print"ok\n";
    }
  }

 foreach (glob("*.ul") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ul file:$pathS/$file "; 
    if (file_exists("$pathS/$file")){unlink("$pathS/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathS/$file");} // Move it into the SOUNDS
    print"ok\n";
    }
  }

 foreach (glob("*.gsm") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing gsm file:$pathS/$file "; 
    if (file_exists("$pathS/$file")){unlink("$pathS/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathS/$file");} // Move it into the SOUNDS
    print"ok\n";
    }
  }
  

// new install GMRS Supermon

exec("unzip $pathR/gmrs.zip",$output,$return_var); 

chdir($pathR);   
 foreach (glob("*.php") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing php file:$pathG/$file "; 
    if (file_exists("$pathG/$file")){unlink("$pathG/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathG/$file");} 
    print"ok\n";
    }
  }
  
 foreach (glob("*.css") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing css file:$pathG/$file "; 
    if (file_exists("$pathG/$file")){unlink("$pathG/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathG/$file");} 
    print"ok\n";
    }
  }  
  
 foreach (glob("*.js") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing js file:$pathG/$file "; 
    if (file_exists("$pathG/$file")){unlink("$pathG/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathG/$file");} 
    print"ok\n";
    }
  } 
 foreach (glob("*.inc") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing inc file:$pathG/$file "; 
    if (file_exists("$pathG/$file")){unlink("$pathG/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathG/$file");} 
    print"ok\n";
    }
  } 
 foreach (glob("*.ini") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ini file:$pathG/$file "; 
    if (file_exists("$pathG/$file")){unlink("$pathG/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathG/$file");} 
    print"ok\n";
    }
  }            
  foreach (glob("*.ico") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ico file:$pathG/$file "; 
    if (file_exists("$pathG/$file")){unlink("$pathG/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathG/$file");} 
    print"ok\n";
    }
  } 
  
exec("unzip $pathR/admin.zip",$output,$return_var); 
//$pathGA = "$pathG/admin";

chdir($pathR);   
 foreach (glob("*.php") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing php file:$pathGA/$file "; 
    if (file_exists("$pathGA/$file")){unlink("$pathGA/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathGA/$file");} 
    print"ok\n";
    }
  }
  
 foreach (glob("*.css") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing css file:$pathGE/$file "; 
    if (file_exists("$pathGA/$file")){unlink("$pathGA/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathGA/$file");} 
    print"ok\n";
    }
  }  
  
 foreach (glob("*.js") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing js file:$pathGE/$file "; 
    if (file_exists("$pathGA/$file")){unlink("$pathGA/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathGA/$file");} 
    print"ok\n";
    }
  } 
 foreach (glob("*.inc") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing inc file:$pathGA/$file "; 
    if (file_exists("$pathGA/$file")){unlink("$pathGA/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathGA/$file");} 
    print"ok\n";
    }
  } 
 foreach (glob("*.ini") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ini file:$pathGA/$file "; 
    if (file_exists("$pathGA/$file")){unlink("$pathGA/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathGA/$file");} 
    print"ok\n";
    }
  }            
  foreach (glob("*.ico") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ico file:$pathGA/$file "; 
    if (file_exists("$pathGA/$file")){unlink("$pathGA/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathGA/$file");} 
    print"ok\n";
    }
  } 

exec("unzip $pathR/images.zip",$output,$return_var); 
//$pathI = "$pathG/images";

chdir($pathR);   
 foreach (glob("*.gif") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing gif file:$pathI/$file "; 
    if (file_exists("$pathI/$file")){unlink("$pathI/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathI/$file");} 
    print"ok\n";
    }
  }
 foreach (glob("*.jpg") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing jpg file:$pathI/$file "; 
    if (file_exists("$pathI/$file")){unlink("$pathI/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathI/$file");} 
    print"ok\n";
    }
  }

//======================================


// "/var/lib/asterisk/sounds/rpt/nodenames";    $pathN 

exec("unzip $pathR/nodenames.zip",$output,$return_var);


 foreach (glob("*.ul") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ul file:$pathN/$file "; 
    if (file_exists("$pathN/$file")){unlink("$pathN/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathN/$file");print"--";} // Move it into the SOUNDS
    print"ok\n";
    }
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
#MENUFT%055%Time/Weather/Node Manager Setup

$SON
reset

php /etc/asterisk/local/mm-software/setup.php

exit 0
';
// overwrite with our menu.
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, $out);flock( $fileOUT, LOCK_UN );fclose ($fileOUT);
 exec("sudo chmod +x $file",$output,$return_var);
 

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
    
   

} 
 
function c641($in){
print"




        **** COMODORE 64 BASIC V2 **** 
 64K RAM SYSTEM  38911 BASIC BYTES FREE
READY.

";
}
