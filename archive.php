#!/usr/bin/php
<?php
// (c)2023 by WRXB288 and LAgmrs.com
// beta version1      Sound archive backup to a FTP server and erase

$user= "user"; $pass= "pass"; $ftp="192.168.0.xxx";

$path         = "/etc/asterisk/local/mm-software";
include ("$path/load.php");
include ("$path/sound_db.php");
$cur   = date('mdyhis');
$archiveDir= "/etc/asterisk/local/log/$node";

check_gsm_db ("silence2");$silence=$file1; $archive="";$action="";
$datum   = date('m-d-Y H:i:s');
$yesterday = date('l m d Y',strtotime("-1 days"));
$today = date('l m d Y');
$date_y= strtolower($yesterday); 
print "
===================================================
Archive backup 
(c)2023 WRXB288 LAGMRS.com all rights reserved
Today:$today Yesterday: $yesterday 
===================================================
";
// build audio timestamp
$action="";
$date_string= explode(' ', $date_y); // Tuesday July 04 2023
check_gsm_db ($date_string[0]);if($file1){$action = "$action $file1";}

$oh=false;
make_number ($date_string[1]);if($file1){$action = "$action $file1";}if($file2){$action = "$action $file2";}
check_gsm_db ("dash");if($file1){$action = "$action $file1";}
make_number ($date_string[2]);if($file1){$action = "$action $file1";}if($file2){$action = "$action $file2";}
check_gsm_db ("dash");if($file1){$action = "$action $file1";}
$x = (string)$date_string[3];
for($i=0;$i<strlen($x);$i++)
 { 
make_number ($x[$i]); 
if($file1){$action = "$action $file1";}
if($file2){$action = "$action $file2";}
}

check_gsm_db ("silence2");if($file1){$action = "$action $file1";}


$timestamp="/tmp/timestamp.gsm";
exec("sox $action $timestamp",$output,$return_var);//print "DEBUG $action";

$action="";


save_task_log ("archive audio files");
chdir($archiveDir);
$ii=0;$ct=0;  $size=0;
foreach (glob("*.WAV") as $file) {
    if($file == '.' || $file == '..') continue;
    $ii++;$ct++;
    $size=filesize($file);if($size==0){print "$file = $size ";continue;}
    $action ="$action $silence $file"; if ($ii>=500){ 
     $cur= date('mdyhis');$archive   = "/etc/asterisk/local/log/archive-$cur.gsm";
     exec("sox $action $archive",$output,$return_var);print"$archive $ii files added,";$ii=0; $action="";}
    }

  
$cur=date('mdyhis');$archive   = "/etc/asterisk/local/log/archive-$cur.gsm";  
exec("sox $action $archive",$output,$return_var);
print"$archive $ii files added <ok>
";

save_task_log ("$archive $ii files added");

print"killing all old files
";
$threshold = strtotime('-1 day');  
foreach (glob("*.WAV") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) {
//        if ($threshold >= filemtime($file)) { unlink($file);print"-";}
       unlink($file);print"-";
    }
}
foreach (glob("*.txt") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) {
//        if ($threshold >= filemtime($file)) { unlink($file);print"-";}
        unlink($file);print"-";
    }
}


    
$datum   = date('m-d-Y H:i:s');
print"$datum found $ct files
--------------------------------------------------
";


chdir("/etc/asterisk/local/log/");


$ii=0;$ct=0;$size=0;$action=""; $file="";
foreach (glob("*.gsm") as $file) {
    if($file == '.' || $file == '..') continue;
    $ii++;$ct++;
    $size=filesize($file);if($size==0){print "$file = $size ";continue;}
    $action ="$action $silence $file"; 
    }
$datum   = date('m-d-Y H:i:s');    
print "$datum Converting and Compressing
";   
// Audio PCM uncompressed 16bit 8khz mono(1 channel)
// Stream #0:0: Audio: gsm, 8000 Hz, mono, s16, 13 kb/s

 
$cur= date('mdyhis');$archive="/etc/asterisk/local/log/archive-$cur.gsm";
$action="$timestamp $action";
exec("sox $action $archive",$output,$return_var);

//exec("curl -T $archive --user $user:$pass ftp://$ftp",$output,$return_var);

$mp3="/etc/asterisk/local/log/archive-$cur.mp3";
exec("ffmpeg -i $archive $mp3",$output,$return_var);

$file = $mp3;
exec("curl -T $file --user $user:$pass ftp://$ftp",$output,$return_var);
$datum   = date('m-d-Y H:i:s');
print"$datum upload  $ct files $file
";
$datum   = date('m-d-Y H:i:s');
print"$datum Erasing"; 
// make sure all archives are removed
foreach (glob("*.gsm") as $file) {
    if($file == '.' || $file == '..') continue;
    unlink ($file); print "-";
    } 
foreach (glob("*.mp3") as $file) {
    if($file == '.' || $file == '..') continue;
    unlink ($file); print "-";
    }       

$datum   = date('m-d-Y H:i:s');
print"
$datum Finished
";
save_task_log ("Uploaded $archive to $ftp");


