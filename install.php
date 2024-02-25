#!/usr/bin/php
<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
// This installer sets up and installs the software. 
// all software is hand coded in php from scratch 
// in North Louisiana.
// -------------------------------------------------------------
//
//  Installs from github.com repo
//
//  PHP Source code is readable in zips at
//  https://github.com/tmastersmart/gmrs_live
//
//  I will never ask for your passwords dont give them to anyone!
//  I do not want remote access to anyones node. Never open a SSH port on 
//  your router to a PI running.
//
//  
//
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
// v4.4 10-25-23   Install tweeks to the startup routines.
// v4.5 11-03-23   Added time and temp to admin menu
// v4.6 12-33-23   Automatic docRoute detection for webserver
// v4.8 1/19/24 Custom install directory. Bug fix in creating main dir
// v4.9 2/1/24  Minor Adjustments to installer.
// v5   2/25      Create the nodelist directory

$phpVersion= phpversion();
$path= "/etc/asterisk/local/mm-software";
$ver="v5.0"; $release="2-25-2024";
$out=""; $in=""; $skip="
























";
print $skip;c641($in);sleep(2);print $skip;

// the APATCHIE config test
$out="";$docRoute="Default";$docRouteP="/srv/http";$changeall=false;$fileEdit="/etc/httpd/conf/httpd.conf"; 
$file="/opt/httpd/httpd.conf"            ;if (file_exists($file)){ $fileEdit=$file;}
$file="/opt/httpd/conf/httpd.conf"       ;if (file_exists($file)){ $fileEdit=$file;}
$file="/etc/httpd/httpd.conf"            ;if (file_exists($file)){ $fileEdit=$file;}
$file="/etc/httpd/conf/httpd.conf"       ;if (file_exists($file)){ $fileEdit=$file;}
$file="/private/etc/apache2/httpd.conf"  ;if (file_exists($file)){ $fileEdit=$file;}
$file="/usr/local/apache/conf/httpd.conf";if (file_exists($file)){ $fileEdit=$file;}
$file="/usr/local/apache2/apache2.conf"  ;if (file_exists($file)){ $fileEdit=$file;}

if (file_exists($fileEdit)){
 $hide=true; $search="DocumentRoot ";search_configI($out);
 if($ok and $out){
 $u = explode(" ",$out); 
 $docRoute=$u[0]; $docRouteP=$u[1]; 
 $docRouteP = str_replace('"', '', $docRouteP);
 } 
} 
print "
   _____ __  __ _____   _____   _           _        _ _           
  / ____|  \/  |  __ \ / ____| (_)         | |      | | |          
 | |  __| \  / | |__) | (___    _ _ __  ___| |_ __ _| | | ___ _ __ 
 | | |_ | |\/| |  _  / \___ \  | | '_ \/ __| __/ _` | | |/ _ \ '__|
 | |__| | |  | | | \ \ ____) | | | | | \__ \ || (_| | | |  __/ |   
  \_____|_|  |_|_|  \_\_____/  |_|_| |_|___/\__\__,_|_|_|\___|_| 

PHP:$phpVersion  Installer:$ver  Release date:$release 
Apache Config Path: $fileEdit
DocumentRoot: $docRouteP
(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
============================================================
 Welcome to the Node Controler and GMRS Supermon system.
  <-Be sure you have made a backup of your memory card->
As with any software its importianat you have a backup.                                           
============================================================
Software will be installed to [$path]
GMRS Supermon will be installed to [$docRouteP] <-Verify  

 i) install
 c) Change  DocumentRoot: $docRouteP <-Verify
 
 Any other key to abort 
";
$a = readline('Enter your command: ');
 if ($a=="c"){ 
  $a = readline('Enter new Path: ') ; 
  $docRouteP = $a;
  print "DocumentRoot changed to: $docRouteP \n\n";
  print "  i) install   Any other key to abort!\n";
  $a = readline('Enter your command: ');
  }



if ($a=="i"){
$path= "/etc/asterisk/local/mm-software"; 
if(!is_dir($path)){ mkdir($path, 0755);}

installa($out);

print " [checking install ....";
chdir($path); print"-";
$file= "$path/mm-node.txt";create_nodea ($file); print"-";

if (file_exists("$path/setup.php")){include ("$path/setup.php");}
else {print "Error install failed! $path/setup.php missing\n";}

print "
Software was installed to [$path]
GMRS Supermon          to [$docRouteP]  


Software Made in loUiSiAna
Thank you for downloading........... And have Many nice days
 

cd $path
php setup.php
";
}
else {
print "
Aborted  Type 'php install.php' to try again\n";}


function installa($in){
global $docRouteP,$path;
// Dual code to be in setup_install.php and install.php
//$docRouteP

$path  = "/etc/asterisk/local/mm-software";if(!is_dir($path)){ mkdir($path, 0755);}        
$repoURL= "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$pathS = "$path/sounds";if(!is_dir($pathS)){ mkdir($pathS, 0755);}
$pathR = "$path/repo";  if(!is_dir($pathR)){ mkdir($pathR, 0755);}
$pathB = "$path/backup";if(!is_dir($pathB)){ mkdir($pathB, 0755);}
$pathNodelist = "$path/nodelist";if(!is_dir($pathNodelist)){ mkdir($pathNodelist, 0755);}
$pathG = "$docRouteP/gmrs";if(!is_dir($pathG)){ mkdir($pathG, 0755);}
$pathGA= "$docRouteP/gmrs/admin";if(!is_dir($pathGA)){ mkdir($pathGA, 0755);}
$pathGE= "$docRouteP/gmrs/edit";if(!is_dir($pathGE)){ mkdir($pathGE, 0755);}
$pathI = "$docRouteP/gmrs/images";if(!is_dir($pathI)){ mkdir($pathI, 0755);}
$pathN = "/var/lib/asterisk/sounds/rpt/nodenames";
 
print"Cleaning any existing repos......\n";
chdir($pathR);

          
$file = "$pathR/core-download.zip"; if (file_exists($file)){unlink ($file);}
$file = "$pathR/sounds.zip"; if (file_exists($file)){unlink ($file);}
$file = "$pathR/supermon.zip";if (file_exists($file)){unlink ($file);}
$file = "$pathR/nodenames.zip";if (file_exists($file)){unlink ($file);}
$file = "$pathR/gmrs.zip"; if (file_exists($file)){unlink ($file);}
$file = "$pathR/admin.zip";if (file_exists($file)){unlink ($file);}
$file = "$pathR/images.zip"; if (file_exists($file)){unlink ($file);}
$file = "$pathR/file_id.diz"; if (file_exists($file)){unlink ($file);}
chdir($pathR);

 print "Downloading new repos ...........\n";
  exec("sudo wget $repoURL/core-download.zip",$output,$return_var);
  exec("sudo wget $repoURL/sounds.zip",$output,$return_var);
//exec("sudo wget $repoURL/supermon-bak/supermon-gmrs-backup.zip",$output,$return_var);// stock supermon for auto repair
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

// install tweaks in startup routine (will be located outside repo directory in path)
$RCfile="/usr/local/etc/rc.allstar";
if (file_exists("$path/rc.allstar.txt")){
 print"Installing rc.allstar file:$RCfile "; 
 if (!file_exists("$RCfile.bak")){ rename ($RCfile, "$RCfile.bak");}// make a backup
 if (file_exists($RCfile)){unlink($RCfile);print"Replacing ";}// kill existing file
 rename ("$path/rc.allstar.txt", $RCfile);// rename and move at the same time
  exec("sudo chmod 755 $RCfile",$output,$return_var); print"CHMOD "; 
 if (file_exists($RCfile)){print"ok\n";}
 else {print"error ";
 if (file_exists("$RCfile.bak")){ 
  rename ("$RCfile.bak", $RCfile);print "Restoring orginal\n";// restore backup
  }
 }

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
global $file,$path,$tts,$node,$call;
//$line= exec("cat /usr/local/etc/allstar_node_info.conf  |egrep 'NODE1='",$output,$return_var);
$file ="/usr/local/etc/allstar_node_info.conf"; // This is a secure file. Contains passwords.
$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$line = str_replace('"', "", $line);
$pos = strpos("-$line", 'NODE1=');   if($pos){ $u= explode("=",$line);$node=$u[1];}
$pos2 = strpos("-$line", 'STNCALL=');if($pos2){$u= explode("=",$line);$call=$u[1];}
}

// /usr/local/etc/tts.conf 
// Get any tss key if exists
$file ="/usr/local/etc/tts.conf";
$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$line = str_replace('"', "", $line);
$pos = strpos("-$line", 'tts_key=');if ($pos){$u= explode("=",$line);$tts=$u[1];}
}

$file= "$path/mm-node.txt";// This will be the AutoNode varable
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node,$call,$tts, , ,");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

admin_sh_menuI("install");
} 


function admin_sh_menuI(){
$file ="/usr/local/sbin/firsttime/adm01-shell.sh";
$file2="/usr/local/sbin/firsttime/mmsoftware.sh";
$file3="/usr/local/sbin/firsttime/mmsoftware2.sh";

// build menu link
if (file_exists($file2)){unlink ($file2);}
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
 
 // build a second menu
if (file_exists($file3)){unlink ($file3);}
copy($file, $file3);// copy existing to get correct permissions
$file= $file3;
$out='#/!bin/bash
#MENUFT%055%Say time and Weather 

$SON
reset

php /etc/asterisk/local/mm-software/weather_pws.php

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


function search_configI($out){ 
global $search,$path,$fileEdit,$ok,$out,$hide;

if(!$hide){print "Search for $search in file:$fileEdit ";}
$ok=false;$line="";
if (file_exists($fileEdit)){
$fileIN= file($fileEdit);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
//print "$line\n";
$pos = strpos("-$line", $search);  
if ($pos>=1){
$out=$line;//print "$line - $search - $out\n"; 
$ok=true;if(!$hide){print"found $out\n";}
break;}
 }
}// end if exists
else {print"File Not Found $fileEdit\n";}

if(!$hide){
 if (!$ok){print"not found $search\n";}
 }
}
 
function c641($in){
print"




        **** COMODORE 64 BASIC V2 **** 
 64K RAM SYSTEM  38911 BASIC BYTES FREE
READY.

";
}
