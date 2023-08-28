#!/usr/bin/php
<?php
// (c)2023 by WRXB288 and LAgmrs.com
// Creates Sound archive backup to a FTP server and purge 
// Works great on large memopry cards or external hard drive.

// Must have a full install to work because it uses the audio database system

// must manualy install

// install ffmpeg into the node before use
// sudo pacman -Sy ffmpeg x264 x265   (select optiopn 1 default options)



// set rpt.conf as follows
//archivedir = /etc/asterisk/local/log/
//				; defines and enables activity recording
//				; into specified directory (optional)
//;archiveaudio=0
//				; Disable saving audio files when
//				; archiving. Use with caution on SDcards
//				; This write a lot of data.
//archivetype=gsm
//				; Allows the selection of gsm (.wav) or
//				; pcm (.ul) for archiving audio files

// Set your ftp server. Im using filzilla on a win10 system.
// Uploading to the net has not been tested. There is no verify.Before erasing

// run by cron at 1am 
// php /etc/asterisk/local/mm-software/archive.php


$ver= "1.3";  // 08/28/2223
$path         = "/etc/asterisk/local/mm-software";

include ("$path/load.php");
include ("$path/sound_db.php");
$cur   = date('mdyhis');
$archiveDir= "/etc/asterisk/local/log/$node";


// my local ftp server on the LAN
$user= ""; $pass= ""; $ftp=""; 

check_gsm_db ("silence2");$silence=$file1; $archive="";$action="";

$datum       = date('m-d-Y H:i:s');
$yesterday   = date('l m d Y',strtotime("-1 days")); 
$curYesterday= date('m-d-y_his',strtotime("-1 days"));
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
$date_string= explode(' ', $date_y); // Yesterdays name
check_gsm_db ($date_string[0]);if($file1){$action = "$action $file1";} 

$oh=false;make_number ($date_string[1]);$action = "$action $actionOut";
check_gsm_db ("dash");if($file1){$action = "$action $file1";}// month
$oh=false;make_number ($date_string[2]);$action = "$action $actionOut";
check_gsm_db ("dash");if($file1){$action = "$action $file1";}//day

$x = (string)$date_string[3];// year
for($i=0;$i<strlen($x);$i++) {
$oh=false;make_number ($x[$i]);$action = "$action $actionOut"; 
}

check_gsm_db ("silence2");if($file1){$action = "$action $file1";}


$timestamp="/tmp/timestamp.gsm";
exec("sox $action $timestamp",$output,$return_var);//print "DEBUG $action";

$action=""; save_task_log ("archive audio files");

chdir("/etc/asterisk/local/log/");

// make sure all archives are removed
foreach (glob("*.gsm") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
foreach (glob("*.mp3") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 



chdir("/etc/asterisk/local/log/$node");


$files = array();
$dir = opendir('.'); // open the cwd..also do an err check.
while(false != ($file = readdir($dir))) {
if($file == '.' || $file == '..') {continue;}
$pos = strpos("-$line", "txt");if($pos){continue;}
$size=filesize($file);if($size==0){continue;}
$files[] = $file; // put in array.
}
natsort($files); // sort.


if($beta){foreach ($files as $line){print "$line\n";}}

$pos = strpos("-$line", "txt");


$ii=0;$ct=0;  $size=0; 
foreach ($files as $file) {
    if($file == '.' || $file == '..') continue;
    $ii++;$ct++;
    $size=filesize($file);if($size==0){print "$file = $size ";continue;}
    print "$ct ";
    $action ="$action $silence $file"; if ($ii>=500){ 
    $archive   = "/etc/asterisk/local/log/raw$ct-$cur.wav";
     exec("sox $action $archive",$output,$return_var);print"
$archive $ii files added,";$ii=0; $action="";}
    }

  
$archive   = "/etc/asterisk/local/log/raw$ct-$cur.wav";  
exec("sox $action $archive",$output,$return_var);
print"
$archive $ii files added <ok>
";
save_task_log ("$archive $ii files added");
 
$datum   = date('m-d-Y H:i:s');
print"
$datum found $ct files
--------------------------------------------------
";


chdir("/etc/asterisk/local/log/");


$ii=0;$ct=0;$size=0;$action=""; $file="";
foreach (glob("*.wav") as $file) {
    if($file == '.' || $file == '..') continue;
    $ii++;
    $size=filesize($file);if($size==0){print "$file = $size ";continue;}
    $ct++;
    $action ="$action $silence $file"; 
    }
$datum   = date('m-d-Y H:i:s');    
print "$datum Converting and Compressing $curYesterday files
";   
// Audio PCM uncompressed 16bit 8khz mono(1 channel)
// Stream #0:0: Audio: gsm, 8000 Hz, mono, s16, 13 kb/s

// merge time audio time stamp and all the files into one
$cur= date('mdyhis');$archive="/etc/asterisk/local/log/archive-$curYesterday.wav";
$action="$timestamp $action $timestamp"; 
exec("sox $action $archive",$output,$return_var);

//exec("curl -T $archive --user $user:$pass ftp://$ftp",$output,$return_var);

$mp3="/etc/asterisk/local/log/archive-$curYesterday.mp3";
exec("ffmpeg -i $archive $mp3",$output,$return_var);

$file = $mp3; $size= filesize($file);
exec("curl -T $file --user $user:$pass ftp://$ftp",$output,$return_var);
$datum   = date('m-d-Y H:i:s');
print"$datum upload  $ct files $file
";
$datum   = date('m-d-Y H:i:s');

$test=($size / 100000);print "$datum FileSize:$size  sleep $test\n"; 
 
sleep ($test); // we have to wait for long uploads to finish before cleaning

print"$datum Cleaning up"; 

// make sure all archives are removed
foreach (glob("*.wav") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file
    ";  }
    } 
foreach (glob("*.mp3") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file
    ";  }
    }       

chdir($archiveDir);
print"killing all old files
";
//$threshold = strtotime('-1 day');  
foreach (glob("*.WAV") as $file) {
    if($file == '.' || $file == '..') continue;
    unlink($file);print"-";
    if(file_exists($file)){print"error del fail
    ";unlink($file);}
}

foreach (glob("*.txt") as $file) {
    if($file == '.' || $file == '..') continue;
    unlink($file);print"-";
    if(file_exists($file)){print"error del fail
    ";unlink($file);}
}

sleep(4);//  wait for it

$count=0;
foreach (glob("*.WAV") as $file) {
    if($file == '.' || $file == '..') continue;
    $count++;  
}



$datum   = date('m-d-Y H:i:s');
print"
$datum Finished  $count files not deleted
";
save_task_log ("Uploaded $archive to $ftp");


