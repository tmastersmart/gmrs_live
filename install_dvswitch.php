#!/usr/bin/php
<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
// This installer sets up and installs the software. 
// all software is hand coded in php from scratch 
// in North Louisiana.
// -------------------------------------------------------------
//

$phpVersion= phpversion();
$path= "/etc/asterisk/local/mm-software";
$ver="v1.0"; $release="6-1-2024";
$out=""; $in=""; $skip="
























";
print $skip;c641($in);sleep(2);print $skip;


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
 Welcome to my pi installer program.
<-Be sure you have made a backup of your memory card->
============================================================
Software will be installed to [$path]


 i) install
 
 Any other key to abort 
";
$a = readline('Enter your command: ');

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

DV Switch setup added to the admin menu.  
You now need to reboot to activate the admin menu.


Software Made in loUiSiAna
Thank you for downloading........... And have Many nice days
 


";
}
else {
print "
Aborted  Type 'php install.php' to try again\n";}

if (file_exists("$path/setup.php")){
$a="";
print "Can not install over existing setup\n
type 'cd $path'    then
type 'php setup.php' to fix install or uninstall\n";
chdir($path);
}


function installa($in){
global $docRouteP,$path;
// Dual code to be in setup_install.php and install.php
//$docRouteP

$path  = "/etc/asterisk/local/mm-software";if(!is_dir($path)){ mkdir($path, 0755);}        
$repoURL= "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$pathR = "$path/repo";  if(!is_dir($pathR)){ mkdir($pathR, 0755);}
$pathB = "$path/backup";if(!is_dir($pathB)){ mkdir($pathB, 0755);}

print"Cleaning any existing repos......\n";
chdir($pathR);

  
$file = "$pathR/dvswitch-download.zip"; if (file_exists($file)){unlink ($file);}
$file = "$pathR/file_id.diz"; if (file_exists($file)){unlink ($file);}
chdir($pathR);

 print "Downloading new repos ...........\n";
  exec("sudo wget $repoURL/dvswitch-download.zip",$output,$return_var);

 print "Downloading finished...........\n";
 chdir($pathR);
  
 exec("unzip $pathR/dvswitch-download.zip",$output,$return_var);
  
     
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
 
admin_sh_menuI("install");

}

function create_nodea ($file){
global $file,$path,$node,$call;
// keep this file up to date
$autotts="";
//if(!$call){$call="hub";}
$file ="/usr/local/etc/allstar_node_info.conf";// only on hamvoip 
if (file_exists($file)){
$autotts=""; 
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
}
$file= "$path/mm-node.txt";$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node,$call, , , ,");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);
}
 


function admin_sh_menuI(){

global $release;
print "$datum Installing into admin menu ";
$file ="/usr/local/sbin/firsttime/adm01-shell.sh";
$file2="/usr/local/sbin/firsttime/dvswitch.sh";
               
copy($file, $file2); print "-";

$formated="#/!bin/bash
#MENUFT%055%DV SWITCH Setup Program Version:$release
";

$out='
$SON
reset

php /etc/asterisk/local/mm-software/dvswitch_setup.php

exit 0
';
$out = "$formated $out";
$fileOUT = fopen($file2, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, $out);flock( $fileOUT, LOCK_UN );fclose ($fileOUT); print "-";
exec("sudo chmod +x $file2",$output,$return_var);
if (file_exists($file2)){print"<ok>\n";}
else{print"<Error>\n";}
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
} 



 
function c641($in){
print"




        **** COMODORE 64 BASIC V2 **** 
 64K RAM SYSTEM  38911 BASIC BYTES FREE
READY.

";
}
