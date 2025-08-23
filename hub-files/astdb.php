#!/usr/bin/php
<?php
// (c)2024/2025 by WRXB288 and LAgmrs.com all rights reserved
// 
// astdb.php drop in replacement.  
//
// v1.2   9/4/24 
// v1.3   9/9/24 Create hub and extra file for directory system.
// v1.4   9/9/24 tweeks removed word repeater from all listings
// v1.6   9/11/24 aded useragent
// v1.7   9/29/24 GMRS HUB version
// v1.8   11/17/24 added gps to hub file
// v2.1   1/10 rebuild con output
// v2.3   special removing the work repeater
// v2.4   3-17  Changed hub detection and added some overides
// $ver = "v2.5"; $release="5/3/2025";
// $ver = "v2.7"; $release="7/1/2025";// censor flag added
// v2.8   7/14 added kludge for uk hub
// v2.9   8/22 added detection for new INVALID flag
// v3.0   8/23 Fix users trying to slip through %quote and better triming that ignores unicode
//
//  call with the cron flag to turn off output 'php astdb.php cron'
$ver = "v3.0"; $release="8/23/2025";$count=0;$cronCon=false;$cron=false; 

$censor=false;// remove all ref to repeaters or freq <----- set this censoring


if (!empty($argv[1])) {   if ($argv[1] =="cron"){$cron=true;}} 

$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_start = $mtime;$in="";$sizeD="";
//$domain ="register.gmrslive.com"; $url = "/cgi-bin/privatenodes.txt"; 
$domain ="nodelist.gmrshub.com" ; $url = ""; 
$path       = "/var/log/asterisk"; //"/var/log/asterisk/astdb.txt";
//$path       = "/tmp";
$log            = "$path/last-log.txt";
$nodelistBU     = "$path/astdb_bu.txt";

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
$datum   = date('m-d-Y H:i:s');$gmdatum = gmdate('m-d-Y H:i:s');$year = gmdate('Y');


print_to_con("===================================================");
print_to_con("GMRHUB Nodelist Update System  $ver");
print_to_con("(c)2023/$year WRXB288 LAGMRS.com all rights reserved");
print_to_con("$phpzone PHP v$phpVersion   Release date:$release");
print_to_con("===================================================");
print_to_con("UTC:$gmdatum");


// If we have a backup install it.
if (!file_exists($nodelist)){
 $out="Nodelist is missing";print_to_con($out);
 if (file_exists($nodelistBU)){ copy($nodelistBU,$nodelist); }
 $out="Restoring nodelist from backup $nodelistBU";print_to_con($out);
}

$update = true;
// only update if db is old  48 hrs min
if (file_exists($nodelist)){
 $ft = time()-filemtime($nodelist);
 if ($ft < 10 * 3600){
 $update=false; $fth=round($ft /3600);
 $out="Nodelist does not need update ($fth hrs) old.";print_to_con($out);
 } 
}
$datum  = date('m-d-Y H:i:s');
// debugging
if (!$cron){ $out="Nodelist Manual Update";print_to_con($out);$update = true;}
if ($update ){
$seconds = mt_rand(0, 1800); $min= round(($seconds / 60),0);
if($cron){$out="Sleep for $min min(s)";print_to_con($out);
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
  $line = ltrim($line); // Removes leading whites
  $pos = strpos("-$line", "Freq. or Description");  
  if (!$pos){
   $pos2 = strpos($line, "|"); 
    if($pos2){ 
    $newLine = "$line\n";
    $compile.= $newLine;
    $i++;}
    }
}
if ($i>=1){
$size = strlen($compile);
$out="Importing Private Nodes $size bytes";print_to_con($out);
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


//print_to_con("OK");


//$out = "$count nodes in database";print_to_con ($out);

$count=0;
sort_nodes ("nodes"); // Builds the database
} // end update
line_end("Line End");

function line_end($in){
global $trust,$poll_time,$trust2,$datum,$mtime,$script_end,$script_start,$soundDbWav,$soundDbGsm,$soundDbUlaw,$out,$cron,$action,$log,$cronCon;
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_end = $mtime;$script_time = ($script_end - $script_start);$script_time = round($script_time,2);
$out = "[$in] Used: $script_time Sec";print_to_con($out);
$out = "===================================================";print_to_con($out);
}

function test_data($html){
global $trust,$poll_time,$trust2,$datum,$out,$cron,$cronCon,$action,$log,$count;
$out= "Testing ";print_to_con($out);
$trust=0;$trust2=false;$test=strtolower($html); 
$pos = strpos($test, "281033");      if($pos){$trust++;$trust2=true;}
$pos = strpos($test, "281036");      if($pos){$trust++;$trust2=true;}
$pos = strpos($test, "node list");   if($pos){$trust++;}

$pos = strpos($test, "gmrshub");    if($pos){$trust++;}
$pos = strpos($test, "do no edit");  if($pos){$trust++;}
if ($trust >=3){$out="<valid> Trust level:$trust ";}
else {$out="<not valid> Trust level:$trust ";}
print_to_con($out);
$out="";
}

function sort_nodes($in){
global $beta,$path,$node,$datum,$cron,$cronCon,$sizeN,$dnsSnap,$astdb,$nodelist,$nodelistTmp,$pathNode,$nodelistExt,$nodelistHub,$action,$log;
global $ver,$release,$censor;
$antiDupe="";$antiDupe2=""; $nodeDupe="";$lastCall="";
$count=0; $countR=0;$countH=0;$countC=0;$spin=0;
$rcount=0;
if(file_exists($nodelistTmp)){

//if (file_exists($nodelist)){
//$ft = time()-filemtime($nodelist);
//if($cron){if ($ft < 24 * 3600 ){print_to_con("Abort filetime=$ft");return;}}
//}

print_to_con("Sorting Nodelist >");

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
if(!isset($u[5])){$u[5]="";}

if (preg_match('#;.*$#m', $u[0]) || preg_match('#[+\-*/]#', $u[0])) {
    $u[0] = "";
}
if ($u[0]>1){

// node|name|city|call|hub|gps
// clean the lines remove all non text html and no ,
$nodeIn= $u[0];
$u[1] = clean_input($u[1]);// name
$u[2] = clean_input($u[2]);// city
$u[3] = clean_input($u[3]);// call (Empty Future expansion)
$typeStore = strtolower($u[4]); // could be Node or Hub ( this is our code field we just save this)
$u[5] = ""; //This is a GPS pos ignore

//test to see if the call has the city state in it
//$pos = strpos("-$u[3]", ","); 
//  if ($pos){
//      $pos = strpos("-$u[2]", ",");//if no city state in city state field then reformat
//       if (!$pos){$u[1]="$u[1] $u[2]"; $u[2]=$u[3]; $u[3]="";}
//} 

// Test for the invalid flag clean it up
$pos = strpos("-$u[2]", "INVALID LOCATION"); if ($pos){$u[2]="Invalid Location,??";}


$test= strtolower("-$u[1] $u[4]"); // field 4 now holds Hub or Node
//$pos = strpos($test, "inactive");if ($pos){$u[0]=0;}


if (strlen($u[1]) > 40) { print_to_con("$u[0] $u[1]"); $u[1] = substr($u[1], 0, 33) . "..."; print_to_con("$u[0] $u[1] Trimed"); }
// Clip long lines

// this is better code but is not installed on all servers
// Check length using multibyte characters
//    if (mb_strlen($u[1], 'UTF-8') > 40) {
//        print_to_con("$u[0] $u[1]");
//        $u[1] = mb_substr($u[1], 0, 33, 'UTF-8') . '...';
//        print_to_con("$u[0] $u[1] Trimed");
//    }




$u[4]="N"; // all default to a node


 $pos = stripos("-$u[1]", "repeater"); // Case-insensitive search
 if ($pos !== false) {
  $u[4] = "R";$rcount++;
//print_to_con("$u[0] $u[1] Replaced R word $rcount"); 
if($censor){$u[1] = str_ireplace("repeater", "...", $u[1]); }// Case-insensitive replace
 }

// These are repeater frequencies (may create mistakes)
$freqs = ["550", "575", "600", "625", "650", "675", "700", "725"];
foreach ($freqs as $freq) {
    if (strpos($u[1], $freq) !== false) {
        $u[4] = "R";
if($censor){$u[1] = str_replace($freq, "...", $u[1]);} // Replace found frequency with "..."
   }
}
// check for the flags first

$pos = strpos($test, "node");     if ($pos){$u[4]="N";}
$pos = strpos($test, "hub");       if ($pos){$u[4]="H";}




// Set known hubs 
$pos = strpos("-$test", "bkfd communication") ;if ($pos){$u[4]="H";}
$pos = strpos("-$test", "slingshot") ;         if ($pos){$u[4]="H";}
$pos = strpos("-$test", "lone wolf") ;         if ($pos){$u[4]="H";}
$pos = strpos("-$test", "upshur county") ;     if ($pos){$u[4]="H";}
$pos = strpos("-$test", "long island gmrs");   if ($pos){$u[4]="H";} 
$pos = strpos("-$test", "thrashednet");        if ($pos){$u[4]="H";} 



// HUB Detection do r first in case of "repeater hub"

$pos = strpos("-$test", "emergency") ;if ($pos){$u[4]="H";} 
$pos = strpos("-$test", "statewide") ;if ($pos){$u[4]="H";}
$pos = strpos("-$test", "nationwide");if ($pos){$u[4]="H";}  

$pos = strpos("-$test", "cloud");    if ($pos){$u[4]="H";}
//$pos = strpos($test, ".com");      if ($pos){$u[4]="H";}
$pos = strpos("-$test", "iax");      if ($pos){$u[4]="H";}
$pos = strpos("-$test", "zello");    if ($pos){$u[4]="H";}
$pos = strpos("-$test", "dvswitch"); if ($pos){$u[4]="H";}  
$pos = strpos("-$test", "dv switch");if ($pos){$u[4]="H";}  
$pos = strpos("-$test", "uk 446");   if ($pos){$u[4]="H";}  





//$pos = strpos($test, "public")    ;if ($pos){$u[4]="R";} 

// this overides above if detected
$pos = strpos($test, "moble");    if ($pos){$u[4]="N";}
$pos = strpos($test, "mobile");   if ($pos){$u[4]="N";} 
$pos = strpos($test, "inactive"); if ($pos){$u[4]="N";}  
$pos = strpos($test, "hotspot");  if ($pos){$u[4]="N";}
$pos = strpos($test, "simplex");  if ($pos){$u[4]="N";}
$pos = strpos($test, "private");  if ($pos){$u[4]="N";}

//print_to_con("$u[0] $test - $u[4]");

$count++;if ($count % 100 == 0) {print_to_con("working $count.....");}

// 281038|WRXB288 - |Mike Mobile Node - |Georgetown, LA|name|
fwrite ($fileOUT1,  "$u[0]|$u[1]|$u[2]|$u[3]|$u[4]|$u[5]|\n"); // reformat nodelist to standard
fwrite ($fileOUT6,  "$u[0]|$u[1]|$u[2]|$u[3]||\n");// super mon output
if ($u[4]=="H"){fwrite ($fileOUT2,  "$u[0]|$u[1]|$u[2]|$u[3]|$u[4]|$u[5]|\n");  }// build hub file

}  // if >1
}  // end for each loop


fclose($fileOUT6);fclose($fileOUT1);fclose($fileOUT2);

$out = "saved by:$ver release:$release  Nodes:$count Bytes:$sizeN";print_to_con($out);

$fileOUT3 = fopen($log ,  "w");fwrite ($fileOUT3,  $action);fclose($fileOUT3);   
 }
}


function load_remote ($in){
global $domain,$url,$datum,$out,$html,$trust,$trust2,$cron,$cronCon,$ver,$action,$log,$count;
// new looping code
$gzip=true;
for ($i = 0; $i < 3; $i++){
$datum  = date('m-d-Y H:i:s');
$out="Polling Nodelist http://$domain/$url";print_to_con($out);
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
$out = substr($html, 0, 70); $out="Received [$out]";print_to_con($out); 

$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);$poll_time = round($poll_time,2);
test_data ($html);
if ($trust >=3){break;}// stop the loop its good
sleep(30); // add a delay before retrying
$gzip=false;
 }// end of the loop
}


function print_to_con($in){ 
global $datum,$cron,$cronCon,$action,$count;
$datum  = date('m-d-Y H:i:s');
  $in = str_replace("\r", "", $in);
  $in = str_replace("\n", "", $in);
$action="$action $datum $in \n";
if(!$cronCon){print "$datum $in\n";}

}

function HandleHeaderLine( $curl, $header_line ) {
global $datum,$out,$cron,$cronCon,$action,$count;
  $out= $header_line; print_to_con($out);
return strlen($header_line);
}


// this prevents users from adding things to the nodelist that can
// break our displays and databases. 
function clean_input($input) {
    // Trim whitespace and remove HTML tags
    $input = trim(strip_tags($input));

    // Decode URL-encoded sequences (e.g., %20, %3A)
    $input = urldecode($input);

    // Remove ASCII control characters (0-31, 127)
    $input = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);

    // Remove invalid URL-encoded sequences (e.g., %quite, %x)
    $input = preg_replace('/%[0-9a-fA-F]{0,1}[^0-9a-fA-F]/u', '', $input);

    // Remove database-sensitive characters (|, ", ', `, \, ;)
    $input = preg_replace('/[|"\'`\\;]/', '', $input);

    // Remove non-printable Unicode and unsafe ranges
$input = preg_replace(
        '/[\x{0000}-\x{001F}\x{007F}-\x{009F}' . // Unicode control characters
        '\x{FDD0}-\x{FDEF}' . // Non-characters
        '\x{FFFE}-\x{FFFF}\x{1FFFE}-\x{1FFFF}\x{2FFFE}-\x{2FFFF}' . // More non-characters
        '\x{E000}-\x{F8FF}' . // Private Use Area
        ']/u',
        '',
        $input
    );

    return $input;
}
?>
