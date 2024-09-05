#!/usr/bin/php
<?php
// (c)2023/2024 by WRXB288 and LAgmrs.com all rights reserved
// astdb.php drop in replacement. Major improved loading and backups
// 
// Brings back gmrs live names and calls to your status page.
//
// v1.2   9/4/24 
$ver = "v1.2"; $release="9-4-2024"; $ver2 ="1-2";

$callsDisplay = true;// set to false to not display calls


$cron=false; if (!empty($argv[1])) {  if ($argv[1] =="cron"){$cron=true;} }
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_start = $mtime;$in="";$sizeD="";
$domain ="register.gmrslive.com"; $url = "/cgi-bin/privatenodes.txt"; 
$path       = "/var/log/asterisk";
$path       = "/tmp";

$nodelistBU     = "$path/astdb_bu.txt";
$nodelistBackup = "$path/nodelist-database-$ver2.csv"; /// The old nodelist
$privatefile    = "/etc/asterisk/local/privatenodes.txt";
$nodelist       = "$path/astdb.txt";  //    the output file
$nodelistTmp    = "$path/astdb.tmp";
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
global $beta,$path,$node,$datum,$cron,$sizeN,$dnsSnap,$astdb,$nodelist,$nodelistTmp,$pathNode,$callsDisplay;

$antiDupe="";$antiDupe2=""; $nodeDupe=""; $spin=0;
$lastCall=""; $count=0; $countR=0;$countH=0;$countC=0;
if(file_exists($nodelistTmp)){


if (file_exists($nodelist)){
$ft = time()-filemtime($nodelist);
if($cron){if ($ft < 24 * 3600 ){return;}}
}

if(!$cron){ print "$datum Sorting Nodelist >";}


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
   
fwrite ($fileOUT6,  "$u[0]|$u[1]|$u[2]|$u[3]|\n");// super mon output
   }  // if >1
 }  // end for each loop
fclose($fileOUT6);
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
