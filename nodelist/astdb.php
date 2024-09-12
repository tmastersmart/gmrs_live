#!/usr/bin/php
<?php
// (c)2024 by WRXB288 and LAgmrs.com all rights reserved
// 
// astdb.php drop in replacement.  
// Brings back gmrs live names and calls to your status page.
// 
// Creates a Comma Separated Values (CSV) database that will overide the nodelist. 
// Edit as you see fit. Allows you to control what displays on your page
// 
// Updates; 
// anytime the database is updated it will change names so that
// any local edits are not lost. Just copy them to the new database.
//
// v1.2   9/4/24 
// v1.3   9/9/24 Create hub and extra file for directory system.
// v1.4   9/9/24 tweeks removed word repeater from all listings
// v1.6   9/11/24 aded useragent
//
$ver = "v1.6"; $release="9-11-2024";$ver2 ="1-4";// the database version

$callsDisplay = true;// set to false to not display calls
$cityDisplay  = true;// replace state with city-state

$cron=false; if (!empty($argv[1])) {  if ($argv[1] =="cron"){$cron=true;} }
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_start = $mtime;$in="";$sizeD="";
$domain ="register.gmrslive.com"; $url = "/cgi-bin/privatenodes.txt"; 
$path       = "/var/log/asterisk"; //"/var/log/asterisk/astdb.txt";
//$path       = "/tmp";
$log            = "$path/last-log.txt";
$nodelistBU     = "$path/astdb_bu.txt";
$nodelistBackup = "$path/nodelist-database-$ver2.csv"; /// The old nodelist
$privatefile    = "/etc/asterisk/local/privatenodes.txt";
$nodelist       = "$path/astdb.txt";  //    the output file
$nodelistClean  = "$path/astdb-clean.txt"; 
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
 $out="Nodelist is missing";print_to_con ($out);
 if (file_exists($nodelistBU)){ copy($nodelistBU,$nodelist); }
 $out="Restoring nodelist from backup $nodelistBU";print_to_con ($out);
}
if (!file_exists($nodelistBackup )){
     installNodelist($in);
     $out="Installing Database $nodelistBackup ";print_to_con ($out);}
$update = true;
// only update if db is old  48 hrs min
if (file_exists($nodelist)){
 $ft = time()-filemtime($nodelist);
 if ($ft < 10 * 3600){
 $update=false; $fth=round($ft /3600);
 $out="Nodelist does not need update ($fth hrs) old.";print_to_con ($out);
 } 
}
$datum  = date('m-d-Y H:i:s');
// debugging
if (!$cron){ $out="Nodelist Manual Update";print_to_con ($out);$update = true;}
if ($update ){
$seconds = mt_rand(0, 1800); $min= round(($seconds / 60),0);
if($cron){$out="Nodelist. Sleep for $min min(s)";print_to_con ($out);
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
$out="Importing Private Nodes $size bytes";print_to_con ($out);
$contents .= $compile;
} 
// get rid of the trash. If this file has nothing but a header get rid of it
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
if(!$cron){print"ok\n";}

$out = "$count nodes in database";print_to_con ($out);

$count=0;
sort_nodes ("nodes"); // Builds the database
} // end update
line_end("Line End");

function line_end($in){
global $trust,$poll_time,$trust2,$datum,$mtime,$script_end,$script_start,$soundDbWav,$soundDbGsm,$soundDbUlaw,$out,$cron,$action,$log;
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_end = $mtime;$script_time = ($script_end - $script_start);$script_time = round($script_time,2);
$out = "[$in] Used: $script_time Sec";print_to_con ($out);
$out = "===================================================";print_to_con ($out);
}

function test_data ($html){
global $trust,$poll_time,$trust2,$datum,$out,$cron,$action,$log;
$out= "Testing ";print_to_con ($out);
$trust=0;$trust2=false;$test=strtolower($html); 
$pos = strpos($test, "2955");      if($pos){$trust++;$trust2=true;}
$pos = strpos($test, "2957");      if($pos){$trust++;$trust2=true;}
$pos = strpos($test, "roadkill");  if($pos){$trust++;} 
$pos = strpos($test, "GMRS");      if($pos){$trust++;}
$pos = strpos($test, "do no edit");if($pos){$trust++;}
if ($trust >=3){$out="<valid> Trust level:$trust [$poll_time Sec.]";}
else {$out="<not valid> Trust level:$trust [$poll_time Sec.]";}
print_to_con ($out);
}

function sort_nodes ($in){
global $beta,$path,$node,$datum,$cron,$sizeN,$dnsSnap,$astdb,$nodelist,$nodelistTmp,$pathNode,$callsDisplay,$cityDisplay,$nodelistExt,$nodelistHub,$action,$log;
global $ver,$release,$ver2;
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
if(!isset($u[4])){$u[4]="";}

if (preg_match('#;.*$#m', $u[0]) || preg_match('#[+\-*/]#', $u[0])) {
    $u[0] = "";
}
if ($u[0]>1){
$u[2] = str_replace("-", "", $u[2]);
$u[2] = str_replace(".", "", $u[2]);
$nodeIn= $u[0];
// replace any new names with the old ones
 if (isset($astdb[$nodeIn])) { 
   $dbNode = $astdb[$nodeIn];
   if(isset($dbNode[1])){$u[1]=$dbNode[1];}
   if($cityDisplay) {if(isset($dbNode[2])){$u[2]=$dbNode[2];}}
   if($callsDisplay){if(isset($dbNode[3])){$u[3]=$dbNode[3];}}  
   if(isset($dbNode[4])){$u[4]=$dbNode[4];}
 }
$count++; 
if(!$cron){
 if ($count % 1500 > 0 && $count % 1500 <= 10) {print".";
  }
}

if ($u[2]==","){$u[2]="";}
$u[2] = trim($u[2]);
fwrite ($fileOUT1,  "$u[0]|$u[1]|$u[2]|$u[3]|$u[4]|\n");    
fwrite ($fileOUT6,  "$u[0]|$u[1]|$u[2]|$u[3]||\n");// super mon output
if ($u[4]=="H"){fwrite ($fileOUT2,  "$u[0]|$u[1]|$u[2]|$u[3]|$u[4]|\n");  }
   }  // if >1
 }  // end for each loop


fclose($fileOUT6);fclose($fileOUT1);fclose($fileOUT2);
if(!$cron){print "ok\n";}
$out ="saved by:$ver release:$release  database:$ver2 Nodes:$count Bytes:$sizeN"; print_to_con ($out);
$fileOUT3 = fopen($log ,  "w");fwrite ($fileOUT3,  "$datum $out\n $action");fclose($fileOUT3);   

 }
}


function load_remote ($in){
global $domain,$url,$datum,$out,$html,$trust,$trust2,$cron,$ver,$action,$log;
// new looping code
$gzip=true;
for ($i = 0; $i < 3; $i++){
$datum  = date('m-d-Y H:i:s');
$out="Polling Nodelist $domain ";print_to_con ($out);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$domain/$url");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
curl_setopt($ch, CURLOPT_USERAGENT, "Nodelist_processor_astdb.php_LAGMRS/$ver");
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


function print_to_con ($out){ 
global $datum,$out,$cron,$action;
$datum  = date('m-d-Y H:i:s');
  $out = str_replace("\r", "", $out);
  $out = str_replace("\n", "", $out);
$action="$action $datum $out \n";
if(!$cron){print "$datum $out\n";}
}

function HandleHeaderLine( $curl, $header_line ) {
global $datum,$out,$cron;
  $out= $header_line; print_to_con ($out);
return strlen($header_line);
}

function installNodelist($in){
global $nodelistBackup;
// install gziped database. First time setup
$encoded="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";
$compressedContent = base64_decode($encoded);
$textContent = gzdecode($compressedContent);
file_put_contents($nodelistBackup, $textContent);
}
