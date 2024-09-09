#!/usr/bin/php
<?php
// (c)2023/2024 by WRXB288 and LAgmrs.com all rights reserved
// astdb.php drop in replacement. Major improved loading and backups
// 
// Brings back gmrs live names and calls to your status page.
//
// v1.2   9/4/24 
// v1.3   9/9/24 Create hub and extra file for directory system. 

$ver = "v1.3"; $release="9-9-2024";
$ver2 ="1-3";// the database version

$callsDisplay = true;// set to false to not display calls


$cron=false; if (!empty($argv[1])) {  if ($argv[1] =="cron"){$cron=true;} }
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_start = $mtime;$in="";$sizeD="";
$domain ="register.gmrslive.com"; $url = "/cgi-bin/privatenodes.txt"; 
$path       = "/var/log/asterisk";
//$path       = "/tmp";

$nodelistBU     = "$path/astdb_bu.txt";
$nodelistBackup = "$path/nodelist-database-$ver2.csv"; /// The old nodelist
$privatefile    = "/etc/asterisk/local/privatenodes.txt";
$nodelist       = "$path/astdb.txt";  //    the output file
$nodelistTmp    = "$path/astdb.tmp";
$nodelistExt    = "$path/astdb-extra.txt";
$nodelistHub    = "$path/astdb-hub.txt";
$flag2          = "/tmp/nodelist_updated.txt";


// Get php timezone in sync with the PI
$phpzone = date_default_timezone_get(); 

$line =	exec('timedatectl | grep "Time zone"'); //       Time zone: America/Chicago (CDT, -0500)
$line = str_replace(" ", "", $line);
$pos1 = strpos($line, ':');$pos2 = strpos($line, '(');
if ($pos1){  $zone   = substr($line, $pos1+1, $pos2-$pos1-1); }
else {$zone="America/Chicago";}

if ($phpzone <> $zone){define('TIMEZONE', $zone);date_default_timezone_set(TIMEZONE);}

$phpzone = date_default_timezone_get(); // test it 
if ($phpzone == $zone ){$phpzone=$phpzone;}
else{$phpzone="$phpzone ERROR";}


$phpVersion= phpversion();
$datum   = date('m-d-Y H:i:s');
$gmdatum = gmdate('m-d-Y H:i:s');
if(!$cron){
print"

===================================================
GMRSLive Nodelist Update System  $ver
(c)2023 WRXB288 LAGMRS.com all rights reserved
$phpzone PHP v$phpVersion   Release date:$release
===================================================

$datum UTC:$gmdatum 
";}

// If we have a backup install it.
if (!file_exists($nodelist)){
 $out="Nodelist is missing";save_task_log ($out);
 if (file_exists($nodelistBU)){ copy($nodelistBU,$nodelist); }
 $out="Restoring nodelist from backup $nodelistBU";save_task_log ($out);
}

if (!file_exists($nodelistBackup )){
     installNodelist($in);
     $out="Installing Database $nodelistBackup ";save_task_log ($out);}

$update = true;
// only update if db is old  48 hrs min
if (file_exists($nodelist)){
 $ft = time()-filemtime($nodelist);
 if ($ft < 10 * 3600){
 $update=false; $fth=round($ft /3600);
 $out="Nodelist does not need update ($fth hrs) old.";save_task_log ($out);
 } 
}


$datum  = date('m-d-Y H:i:s');
// debugging
if (!$cron){ $out="Nodelist Manual Update";save_task_log ($out);$update = true;}

if ($update ){
$seconds = mt_rand(0, 1800); $min= round(($seconds / 60),0);
if($cron){$out="Nodelist. Sleep for $min min(s)";save_task_log ($out);
sleep($seconds);
}

load_remote ($in);


$datum  = date('m-d-Y H:i:s');
if ($trust <=2 or !$trust2){line_end("ERROR BAD DATA");}

$contents="";
if (file_exists($privatefile)) {
// test for private nodes and erase the bad header
$fileIN= file($privatefile);$compile="";$i=0;
foreach($fileIN as $line){
  $line = str_replace("\r", "", $line);
  $line = str_replace("\n", "", $line);
  $pos = strpos($line, "Freq. or Description");  
  if (!$pos){
   $pos2 = strpos($line, "|"); 
    if($pos2){ $compile="$compile $line\n";$i++;}
    }
}
if ($i>=1){
$size = strlen($compile);
$out="Importing Private Nodes $size bytes";save_task_log ($out);
$contents .= $compile;
} 
// get rid of the trash. If this file has nothing but a header get rid of it
// Default image has a node 1000 header that we dont want to import.
// This looks to be a bug that could cause trouble for the real node 1000
else {unlink($privatefile);} 
}

$contents .= $html;
$contents = preg_replace('/[\x00-\x09\x0B-\x0C\x0E-\x1F\x7F-\xFF]/', '', $contents);

if(file_exists($nodelist)){
 if(file_exists($nodelistBU)){unlink($nodelistBU);}
 copy($nodelist,$nodelistBU);
} // keep backups

$fileOUT = fopen($nodelistTmp,'w');fwrite ($fileOUT,$contents);fclose ($fileOUT);  // save in the tmp file
$sizeN = strlen($contents);                                                           


// load old nodelist into memory
if(!$cron){ print"$datum Loading Database >";}
$astdb = array(); $file=$nodelistBackup;$count=0;
$fileIN= file($file); 
foreach($fileIN as $line){ 
$u = preg_split("/\,/", trim($line));
if(!isset($u[1])){$u[1]="";}
if(!isset($u[2])){$u[2]="";}
if(!isset($u[3])){$u[3]="";}
if(!isset($u[4])){$u[4]="";}
$nodeIn=$u[0];
$astdb[$nodeIn] = $u;// using node # as a index key
$count++;  
if(!$cron){
 if ($count % 1500 > 0 && $count % 1500 <= 10) { print ".";}
}
}
if(!$cron){print"ok\n";
print "$datum $count nodes in database \n";}
$count=0;

sort_nodes ("nodes"); // Builds the database
} // end update


line_end("Line End");


function line_end($in){
global $trust,$poll_time,$trust2,$datum,$mtime,$script_end,$script_start,$soundDbWav,$soundDbGsm,$soundDbUlaw,$out,$cron;
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_end = $mtime;$script_time = ($script_end - $script_start);$script_time = round($script_time,2);
$out = "[$in] Used: $script_time Sec";save_task_log ($out);
$out = "===================================================";save_task_log ($out);

}


function test_data ($html){
global $trust,$poll_time,$trust2,$datum,$out,$cron;
$out= "Testing ";save_task_log ($out);
$trust=0;$trust2=false;$test=strtolower($html); 
$pos = strpos($test, "2955");      if($pos){$trust++;$trust2=true;}
$pos = strpos($test, "2957");      if($pos){$trust++;$trust2=true;}
$pos = strpos($test, "roadkill");  if($pos){$trust++;} 
$pos = strpos($test, "GMRS");      if($pos){$trust++;}
$pos = strpos($test, "do no edit");if($pos){$trust++;}
if ($trust >=3){$out="<valid> Trust level:$trust [$poll_time Sec.]";save_task_log ($out);}
else {$out="<not valid> Trust level:$trust [$poll_time Sec.]";save_task_log ($out);}
}





function sort_nodes ($in){
global $beta,$path,$node,$datum,$cron,$sizeN,$dnsSnap,$astdb,$nodelist,$nodelistTmp,$pathNode,$callsDisplay,$nodelistExt,$nodelistHub;

$antiDupe="";$antiDupe2=""; $nodeDupe=""; $spin=0;
$lastCall=""; $count=0; $countR=0;$countH=0;$countC=0;
if(file_exists($nodelistTmp)){


if (file_exists($nodelist)){
$ft = time()-filemtime($nodelist);
if($cron){if ($ft < 24 * 3600 ){return;}}
}

if(!$cron){ print "$datum Sorting Nodelist >";}


$fileOUT1 =fopen($nodelistExt ,  "w");  // writes a nodelist with extra type data
$fileOUT2 =fopen($nodelistHub ,  "w");  // writes a nodelist with hubs
$fileOUT6 =fopen($nodelist ,  "w"); // writes the standard file for supermon


$fileIN= file($nodelistTmp);
natsort($fileIN);
foreach($fileIN as $line){
 //Remove line feeds
  $line = str_replace("\r", "", $line);
  $line = str_replace("\n", "", $line);
  $line = str_replace('"' , "", $line);// get rid of the quotes
  
$u = explode("|",$line);
// Extra error checking
if(!isset($u[0])){$u[0]="";}
if(!isset($u[1])){$u[1]="";}
if(!isset($u[2])){$u[2]="";}
if(!isset($u[3])){$u[3]="";}

//if(isset($u[4])){$u[1]="$u[1] - $u[4]";} // error checking for New fields
$u[4]="";$u[5]="";$u[6]="";// just blank these for my use. Change later

if (preg_match('#;.*$#m', $u[0]) || preg_match('#[+\-*/]#', $u[0])) {
    $u[0] = "";
}

if ($u[0]>1){


$u[2] = str_replace("-", "", $u[2]);
$u[2] = str_replace(".", "", $u[2]);



$nodeIn= $u[0];

// replace ann new names with the old ones
 if (isset($astdb[$nodeIn])) { 
   $dbNode = $astdb[$nodeIn];
   if(isset($dbNode[1])){$u[1]=$dbNode[1];}
   if(isset($dbNode[2])){$u[2]=$dbNode[2];}
   if(isset($dbNode[4])){$u[4]=$dbNode[4];}
 if ($callsDisplay){
    if(isset($dbNode[3])){$u[3]=$dbNode[3];}
    }
  }







$count++; 
if(!$cron){
 if ($count % 1500 > 0 && $count % 1500 <= 10) {print".";
  }
}


  
if ($u[2]==","){$u[2]="";}
$u[2] = trim($u[2]);
fwrite ($fileOUT1,  "$u[0]|$u[1]|$u[2]|$u[3]|$u[4]|\n");    
fwrite ($fileOUT6,  "$u[0]|$u[1]|$u[2]|$u[3]|\n");// super mon output

if ($u[4]=="H"){fwrite ($fileOUT2,  "$u[0]|$u[1]|$u[2]|$u[3]|$u[4]|\n");  }


   }  // if >1
 }  // end for each loop
fclose($fileOUT6);fclose($fileOUT1);fclose($fileOUT2);
if(!$cron){print "ok\n$datum Built Nodelist Database Nodes:$count Bytes:$sizeN\n";}


}

}

//$domain ="register.gmrslive.com"; $url = "/cgi-bin/privatenodes.txt"; 
function load_remote ($in){
global $domain,$url,$datum,$out,$html,$trust,$trust2,$cron;
// new looping code
$gzip=true;
for ($i = 0; $i < 3; $i++){

$datum  = date('m-d-Y H:i:s');
$out="Polling Nodelist $domain ";save_task_log ($out);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$domain/$url");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
if ($gzip){curl_setopt($ch, CURLOPT_ENCODING, 'gzip');}
curl_setopt($ch, CURLOPT_TIMEOUT, 20);	 	
curl_setopt($ch, CURLOPT_HEADERFUNCTION, "HandleHeaderLine");
$html=curl_exec($ch);
curl_close($ch);
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_start = $mtime;
$datum  = date('m-d-Y H:i:s');
$out = substr($html, 0, 200);
if (!$cron){print"$datum Received\n$out\n";}



$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);$poll_time = round($poll_time,2);

test_data ($html);

if ($trust >=3){break;}// stop the loop its good
             
sleep(30); // add a delay before retrying
$gzip=false;
 }// end of the loop
}


function save_task_log ($out){ 
global $datum,$out,$cron;
$datum  = date('m-d-Y H:i:s');
  $out = str_replace("\r", "", $out);
  $out = str_replace("\n", "", $out);

if(!$cron){print "$datum $out\n";}
}

function HandleHeaderLine( $curl, $header_line ) {
global $datum,$out,$cron;
  $out= $header_line; save_task_log ($out);
return strlen($header_line);
}

function installNodelist($in){
global $nodelistBackup;
$encoded="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";
$compressedContent = base64_decode($encoded);
$textContent = gzdecode($compressedContent);
file_put_contents($nodelistBackup, $textContent);
}
