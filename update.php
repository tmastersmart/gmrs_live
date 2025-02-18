#!/usr/bin/php
<?php
//  ------------------------------------------------------------
//  (c) 2023/2025 by WRXB288 lagmrs.com all rights reserved
//
//  Node image upgrade system. 
// -------------------------------------------------------------
// 1.9 09/18/23 added cleanup of license files and linefeeds
// 2.0 09/29/23 Changes to allow for promptless updates later
// 2.1 10/02/23 Stop backing up the log
// 2.2 10/24/23 Installs my tweeks in the startup routine
// 2.3 11/12/23 make extra backups rpt iax
// 2.4 12/23/23 Checks the docRoute before installing. 
// 2.5 1/2/24 Changes to search and edit module requires update
// 2.6 1/19   New custom install directory $docRouteP passed through from setup files
// 2.7 1/24  Support for new sounds. erase old sounds before reinstall.
// 3.1 6/14/24  There was a bug in the reistall of our nodes audio file.
// 3.2.1 2/10/25  Rebuild upgrade for image  

$verInstaller= "3.3"; $verRt="2-18-2025"; $changeall=false;
$year = date("Y");
print "



























-----------------------------------------------------------------------------
System Update Module $verInstaller Release Date:$verRt
(c) 2023/$year by WRXB288 lagmrs.com all rights reserved 
-----------------------------------------------------------------------------


This will Update the Node image to the currect release.

Be sure you have backups.  Use Win32DiskImager in read mode to make backups. 

If you want to see whats in this update the code is at github for inspection.
https://github.com/tmastersmart/gmrs_live   (certified safe)

u) Update (any other key to abort!)

";


 $a = readline('Enter your command: ');
 if ($a <> "u"){die;}


$docRouteP="/srv/http";        
$path  = "/etc/asterisk/local/mm-software";        
$repoURL= "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";  
$pathS = "$path/sounds";//if(!is_dir($pathS)){ mkdir($pathS, 0755);}
$pathR = "$path/repo"; if(!is_dir($pathR)){ mkdir($pathR, 0755);}
$pathB = "$path/backup";if(!is_dir($pathB)){ mkdir($pathB, 0755);}
$pathG = "$docRouteP/status";//if(!is_dir($pathG)){ mkdir($pathG, 0755);}
$pathGA= "$docRouteP/admin";//if(!is_dir($pathGA)){ mkdir($pathGA, 0755);}
$pathGE= "$docRouteP/images";//if(!is_dir($pathGE)){ mkdir($pathGE, 0755);}
$pathI = "$docRouteP/status/images";//if(!is_dir($pathI)){ mkdir($pathI, 0755);}
$pathF = "/usr/local/sbin/firsttime";
$pathN = "/var/lib/asterisk/sounds/rpt/nodenames";
 

print "Running Live upgrade system ...........\n";
 chdir($pathR);


if (file_exists("$pathR/version.txt")) {rename ("$pathR/version.txt", "$path/version-new.txt");}
else {print "Cant find new version info So quiting. Did we even download anything? \n";
print "Press ANY Key\n";
$a = readline('Ready: ');
die;
} 
  


 
 
 //just to be safe backup or core files.
$backupFile = "$path/backup/core-backup.tar.gz"; $command = "tar -czvf $backupFile $path/*.php";exec($command, $output, $return_var);
if ($return_var === 0) {echo "Backup core successful: $backupFile\n";}
else {echo "Backup core failed!\n";} 

 //just to be safe backup or core files.
$backupFile = "$path/backup/core-backup-csv.tar.gz"; $command = "tar -czvf $backupFile $path/*.csv";exec($command, $output, $return_var);
if ($return_var === 0) {echo "Backup csv core successful: $backupFile\n";}
else {echo "Backup core failed!\n";} 
 

 
 
 
// first check for core
 if (file_exists("$pathR/core-download.zip")) { 
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
 
}
// end of the core files. 



// sounds files
if (file_exists("$pathR/sounds.zip")){ 
// for this version we are only installing new files
// chdir($pathS); clean_repo($pathS);  
chdir($pathR); 
exec("unzip $pathR/sounds.zip",$output,$return_var);
 
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
}
// end new sounds  


// install the status page updates
if (file_exists("$pathR/status.zip")){
exec("unzip $pathR/status.zip",$output,$return_var); 

chdir($pathR); 

//if (file_exists("$pathR/setup.edit.php")){unlink("$pathR/setup.edit.php");}// Make sure this file is not in non admin area.
  
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
           
  foreach (glob("*.csv") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing csv file:$pathG/$file "; 
    if (file_exists("$pathG/$file")){unlink("$pathG/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathG/$file");} 
    print"ok\n";
    }
  } 
  
  foreach (glob("*.txt") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing txt file:$pathG/$file "; 
    if (file_exists("$pathG/$file")){unlink("$pathG/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathG/$file");} 
    print"ok\n";
    }
  } 
  
}
// end of status page updates


if (file_exists("$pathR/admin.zip")){
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

 foreach (glob("*.csv") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ini file:$pathGA/$file "; 
    if (file_exists("$pathGA/$file")){unlink("$pathGA/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathGA/$file");} 
    print"ok\n";
    }
  }            

}
// end of admin

if (file_exists("$pathR/images-s.zip")){

exec("unzip $pathR/images-s.zip",$output,$return_var); 
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
 foreach (glob("*.png") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing png file:$pathI/$file "; 
    if (file_exists("$pathI/$file")){unlink("$pathI/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathI/$file");} 
    print"ok\n";
    }
  }
  
    foreach (glob("*.ico") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ico file:$pathGA/$file "; 
    if (file_exists("$pathI/$file")){unlink("$pathI/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathI/$file");} 
    print"ok\n";
    }
  } 
}
// end images  
  
if (file_exists("$pathR/images.zip")){

exec("unzip $pathR/images.zip",$output,$return_var); 
//$pathI = "$pathG/images";

chdir($pathR);   
 foreach (glob("*.gif") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing gif file:$pathI/$file "; 
    if (file_exists("$pathI2/$file")){unlink("$pathI2/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathI2/$file");} 
    print"ok\n";
    }
  }
 foreach (glob("*.jpg") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing jpg file:$pathI/$file "; 
    if (file_exists("$pathI2/$file")){unlink("$pathI2/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathI2/$file");} 
    print"ok\n";
    }
  }
 foreach (glob("*.png") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing png file:$pathI/$file "; 
    if (file_exists("$pathI2/$file")){unlink("$pathI2/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathI2/$file");} 
    print"ok\n";
    }
  }
  
    foreach (glob("*.ico") as $file) {
  if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing ico file:$pathGA/$file "; 
    if (file_exists("$pathI2/$file")){unlink("$pathI2/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathI2/$file");} 
    print"ok\n";
    }
  } 
}
// end images    
//======================================


// "/var/lib/asterisk/sounds/rpt/nodenames";    $pathN

if (file_exists("$pathR/nodenames.zip")){ 
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
// end nodenames. we wont update this very often
  

// this is the admin menu install 
if (file_exists("$pathR/firsttime.zip")){ 
 exec("unzip $pathR/firsttime.zip",$output,$return_var);


 foreach (glob("*.sh") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing sh file:$pathN/$file "; 
    if (file_exists("$pathF/$file")){unlink("$pathF/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$pathF/$file");print"--";} // Move it into the SOUNDS
    exec("sudo chmod 755 $pathF/$file",$output,$return_var); print"CHMOD 755"; 
    print"ok\n";
    }
  }
}
// end menus. Admin may need to be added later   
  
// --------------------- expansion replace others at later date ----------------------- 
  
  
  
  
  
 
  
$pathB = "$path/backup";

// make backups, erase the old ones to save space
$file = "$path/setup.txt";   $file2= "$pathB/setup.txt";  if (file_exists($file2)){unlink($file2);} 
copy($file, $file2);print "Backup $file2\n";
$file = "$path/mm-node.txt"; $file2= "$pathB/mm-node.txt";if (file_exists($file2)){unlink($file2);}
copy($file, $file2);print "Backup $file2\n";
$file = "$path/logs/log.txt";$file2= "$pathB/log.txt";    if (file_exists($file2)){unlink($file2);}
copy($file, $file2);print "Backup $file2\n";
$file = "/etc/asterisk/rpt.conf";$file2= "$pathB/rpt.cfg";if (file_exists($file2)){unlink($file2);}
copy($file, $file2);print "Backup $file2\n";
$file = "/etc/asterisk/iax.conf";$file2= "$pathB/iax.cfg";if (file_exists($file2)){unlink($file2);}
copy($file, $file2);print "Backup $file2\n";
$file = "/etc/asterisk/manager.conf";$file2= "$pathB/manager.cfg";    if (file_exists($file2)){unlink($file2);}
copy($file, $file2);print "Backup $file2\n";




 print "----\n";

chdir($pathR); clean_repo($pathR);
// cleanup  old unused files 
$file="$path/license-sounds.txt";if (file_exists($file)){unlink($file);}
$file="$path/license-core.txt";  if (file_exists($file)){unlink($file);}
$file="$path/license-web.txt";   if (file_exists($file)){unlink($file);}

if (file_exists("$path/version-new.txt")) {
 $file="$path/version.txt";       if (file_exists($file)){unlink($file);}
rename ("$path/version-new.txt", "$path/version.txt"); 
print "Updating version.txt\n";
}
else{print "Unknown error \n";}





print "Finished Upgrade. In most cases this is a live update. No reboot needed.!\n";
print "Admin screens wont change unless you logout and back in. \n";
print "Louisiana Image its just better!\n\n";
print "Press ANY Key\n";
$a = readline('Ready: ');
die;



function clean_repo($in){
chdir($in);
$files = glob($in.'/*'); 
foreach($files as $filed) {
   if($filed == '.' || $filed == '..') continue;
   if (is_file($filed)) { unlink($filed);print"del $filed\n";  }
 }
}




?>
