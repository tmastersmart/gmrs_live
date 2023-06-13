<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
// Setup program
//
// -------------------------------------------------------------

// PHP is in UTC Get in sync with the PI
$line =	exec('timedatectl | grep "Time zone"'); //       Time zone: America/Chicago (CDT, -0500)
$line = str_replace(" ", "", $line);
$pos1 = strpos($line, ':');$pos2 = strpos($line, '(');
if ($pos1){  $zone   = substr($line, $pos1+1, $pos2-$pos1-1); }
else {$zone="America/Chicago";}
define('TIMEZONE', $zone);
date_default_timezone_set(TIMEZONE);
$phpzone = date_default_timezone_get(); // test it 
if ($phpzone == $zone ){$phpzone="$phpzone set";}
else{$phpzone="$phpzone ERROR";}

$phpVersion= phpversion();

$ver="v1.0";
$in="";
$path= "/etc/asterisk/local/mm-software";
// automatic node setup
$file= "$path/mm-node.txt";
if(!file_exists($file)){create_node ($file);}
if(file_exists($file)){
$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$u= explode(",",$line);$AutoNode=$u[0];
}
if (!$AutoNode){
$datum = date('m-d-Y-H:i:s');
print"$datum Error loading node number";}
}
$stripe="============================================================";
start("start");

function start($in){
global $AutoNode,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;

global  $phpzone,$phpVersion; 
  load($in);
print "

(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
$phpzone PHP v$phpVersion
$stripe
Welcome 
  
 Setup.php  program 
          
 1) Edit Setup
 2) check if registered and ping everyone
 3) Speedtest
 D) View the Docs
 U) Upgrade (get the latest version)
 E) Exit  
$stripe
";




$stdin = fopen('php://stdin', 'r');
$yes   = false;

while (!$yes)
{
    $datum = date('m-d-Y-H:i:s');
	print "$datum Enter 1-2 E:_";
	$input = trim(fgets($stdin));
    if ($input == '1'){edit("edit");}
    if ($input == '2'){ping("ping");}
    if ($input == '3'){speedtest("speed");}
    if ($input == 'd'){doc("view");}     
   	if ($input == 'u'){install($out);} 
	if ($input == 'e'){quit('EXIT');}
    if ($input == ''){quit('EXIT');}
}
}

function ping($in){
  global $stripe,$path;
  print "
$stripe  
Please wait for checks to run.......
Be aware that the register server does not answer to ping
";
  exec("sudo bash check_gmrs.sh >$path/ping.txt",$output,$return_var);
  
  $out = file_get_contents("$path/ping.txt");
  print "$out
  ";
  
 
}

function speedtest($in){
  global $stripe,$path;
  print "
$stripe  
Please wait for speedtest to run.......
Its slow, to run from command line type
speedtest-cli

";
  exec("speedtest-cli >$path/ping.txt",$output,$return_var);
  
$out = file_get_contents("$path/ping.txt");
print "$out
 ";
  
 
}
function quit($in){
global $path,$stripe;
$path= "/etc/asterisk/local/mm-software";
//if(file_exists("$path/ping.txt")){unlink "$path/ping.txt";}
print "
$stripe
Thank you for downloading.........Made in Louisiana



* SLMR v2.1 * Have many nice days.
* ";
die;
}


function edit($in){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;

editmenu();


$stdin = fopen('php://stdin', 'r');
$yes   = false;

while (!$yes)
{
    $datum = date('m-d-Y-H:i:s');
	print "$datum Enter 1-7 M E:_";
	$input = trim(fgets($stdin));
    if ($input == '1'){madis($station);}
    if ($input == '2'){level($level);}
    if ($input == '3'){zip($zipcode);}
    if ($input == '4'){warn($zipcode);}
    if ($input == '5'){location($lon);}
    if ($input == '6'){cpu($hot);}
    if ($input == '7'){setnode($node);}
    if ($input == '8'){forcast("set");}
    if ($input == '9'){beta("set");}
    if ($input == 'm'){start("start");}
    if ($input == 'w'){save("save");}
	if ($input == 'e'){quit('EXIT');}
}

}

function forcast($in){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayForcast="false";$displayBeta="false";
if ($forcast) {    $displayForcast  ="true";}
if ($beta){        $displayBeta     ="true";}
print "
NWS forcast will be played after time if set. Forcast is set to $displayForcast
(this is still in beta)
";
$line = readline("Set to t/f: ");
if ($line=="t"){$forcast=true;}
if ($line=="f"){$forcast=false;}
editmenu();
}



function beta($in){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayForcast="false";$displayBeta="false";
if ($forcast) {    $displayForcast  ="true";}
if ($beta){        $displayBeta     ="true";}
print "
Beta features are still being tested and may have problems.
Beta is set to $displayBeta
";
$line = readline("Set to t/f: ");
if ($line=="t"){$beta=true;}
if ($line=="f"){$beta=false;}
editmenu();
}



function setnode($station){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";
print "
Node1 is auto detected on install if you need to change this you can do so here

AutoDetect node:$AutoNode Node set to $node:

";
$line = readline("Node $node: ");
if($line){$node=$line;}
editmenu();

}


function madis($station){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";
print "
find your local MADIS station at 
https://madis-data.ncep.noaa.gov/MadisSurface/

APRSWXNET/Citizen Weather Observer Program 

";
$line = readline("Local Station $station: ");
$station = $line;
editmenu();
}
function level($level){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";
print "
When the time and temp runs how much detal do you want?
1=temp,2=temp,cond 3=temp,cond,wind,hum,rain

You know you want it all select 3

";
$line = readline("Level $level: ");
$station = $line;
editmenu();
}

function zip($level){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";
print "
 Acuweather gives us conditions, It needs a zipcode [$zipcode] 
";
$line = readline("ZipCode $zipcode: ");
$station = $line;
editmenu();
}

function cpu($level){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";
if ($reportAll)   {$displayReport   ="true";}
print "
 CPU Temp setup  HiTemp[$high]  Hot[$hot] All temps[$displayReport]
 HiTemp is the alarm
 Hot is a warning
 All temp either reports temp on every call or only on hot events.
";
$line = readline("HiTemp default 60: ");
if ($line>55){$high=$line;}

$line = readline("HotTemp default 50: ");
if ($line>49){$hot=$line;}

$line = readline("Report all temps y/n: ");
if ($line=="y"){$reportAll=true;}
if ($line=="n"){$reportAll=false;}

editmenu();
}

function location($in){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;

print "
 Location for new NWS api  [$lat]/[$lon] 
";
$line = readline("Lat $lat: ");
$lat = $line;
$line = readline("Lon $lon: ");
$lon = $line;


editmenu();
}

function doc($in){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate,$stripe;
$i=0;
$file="$path/readme.txt";
print "
 Loading the readme.txt file
";
$fileIN= file($file);
foreach($fileIN as $line){
print $line; $i++ ;
if ($i >42){$line = readline("Hit return to Cont: ");$i=0;print "$stripe
";}
}

$line = readline("Hit return to Cont: ");
start("start");
}

function warn($skywarn){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false"; 
if ($sayWatch)    {$displayWatch =   "true";}
if ($sayWarn)     {$displayWarn     ="true";}
if ($sayAdvisory) {$displayAdvisory ="true";}
if ($sayStatement){$displayStatement="true";}

print "
Skywarn uses Common Alerting Protocol (CAP) from the NWS
it needs a code for your area. 
Go to https://alerts.weather.gov and get the code for your county 
";
$line = readline("Code $skywarn: ");
if($line){$skywarn = $line;}

print "
Customise what reports you want 
SayWatch     $displayWatch
SayAdvisory  $displayAdvisory
SayStatement $displayStatement 
 
";
$line = readline("Say Watches y/n: ");
if ($line=="y"){$sayWatch=true;}
if ($line=="n"){$sayWatch=false;}

$line = readline("Say Advisory y/n: ");
if ($line=="y"){$sayAdvisory=true;}
if ($line=="n"){$sayAdvisory=false;}

$line = readline("Say Statement y/n: ");
if ($line=="y"){$sayStatement=true;}
if ($line=="n"){$sayStatement=false;}
editmenu();
}



function editmenu(){
global $forcast,$beta,$AutoNode,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";$displayForcast="false";$displayBeta="false";
if ($sayWatch)    {$displayWatch =   "true";}
if ($sayWarn)     {$displayWarn     ="true";}
if ($sayAdvisory) {$displayAdvisory ="true";}
if ($sayStatement){$displayStatement="true";}
if ($forcast) {    $displayForcast  ="true";}
if ($beta){        $displayBeta     ="true";}
if ($reportAll)   {$displayReport   ="true";}
print"

$stripe
 Setup editor.   Last write date:$saveDate
      
 1) Station [$station] (MADIS/CWOP) Station  
 2) Level   [$level] 1=temp,2=temp,cond 3=temp,cond,wind,hum,rain 
 3) Zipcode [$zipcode] for Acuweather conditions
 4) Skywarn [$skywarn] SayWatch:$displayWatch SayAdvisory:$displayAdvisory SayStatement:$displayStatement 
 5) Location Lat:[$lat]/Lon:[$lon] for NWS
 6) CPU Temp setup  HiTemp[$high]  Hot[$hot] All temps[$displayReport]
 7) Node   Auto:[$AutoNode]  Node:[$node]
 8) Forcast [$displayForcast] 
 9) Bata features [$displayBeta]
 W) Write to file.
 M) Main menu
 E) Exit  
$stripe
";



}

function load($in){
global $datum,$forcast,$beta,$saveDate,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;


if (is_readable("$path/setup.txt")) {
   $fileIN= file("$path/setup.txt");
//$datum = date('m-d-Y-H:i:s');
//print "$datum Loading settings
//";
   foreach($fileIN as $line){
    $u = explode(",",$line);
//            $path =  $u[0];
             $node  =  $u[1];
          $station  =  $u[2];
             $level =  $u[3];
         $zipcode   =  $u[4];
           $skywarn =  $u[5];
               $lat =  $u[6];
               $lon =  $u[7];
           $sayWarn =  $u[8];
          $sayWatch =  $u[9];
       $sayAdvisory = $u[10];
      $sayStatement = $u[11];
        $testNewApi = $u[12];
              $high = $u[13]; 
               $hot = $u[14];
          $nodeName = $u[15];
         $reportAll = $u[16];
          $saveDate = $u[17];
           $forcast = $u[18];
              $beta = $u[19];
    }
}



else {
// set default
$status ="Building default settings";save_task_log ($status);
print "$datum $status
";

$reportAll = false;$nodeName = "server";$high = 60;$hot  = 50;
$station="KIER";$level = 3 ;
$zipcode="71432"; $skywarn ="LAC043"; $lat ="31.7669"; $lon="-92.3888";
$sayWarn=true;$sayWatch=true;$sayAdvisory=true;$sayStatement =true;$node = $AutoNode;$forcast= false;$beta = false;
save ("save");
}
}


function save($in){
global $datum,$forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;
$fileOUT = fopen("$path/setup.txt", "w");
$status ="Writing settings";save_task_log ($status);
print "$datum $status
";
fwrite ($fileOUT, "$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$datum,$forcast,$beta,,,,,,");
fclose ($fileOUT);
}


function install($in){
global $datum,$file,$path;
$files = "clear.wav,flood_advisory.wav,weather_service.wav,hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";
$path  = "/etc/asterisk/local/mm-software";
$path2 = "$path/sounds";

$u = explode(",",$files);
if(!is_dir($path)){ mkdir($path, 0755);}
chdir($path);
if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2);
$repo="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/sounds";
$datum = date('m-d-Y-H:i:s');
print"
$datum Checking for missing sounds
";

foreach($u as $file) {
if (!file_exists("$path2/$file")){
   print "sudo wget $repo/$file
   "; 
exec("sudo wget $repo/$file",$output,$return_var);
   }
   }
// install other
$files = "supermon.txt,config.php,setup.php,forcast.php,temp.php,skywarn.php,weather_pws.php,sound_db.php,check_gmrs.sh,sound_db.php,sound_wav_db.csv,sound_gsm_db.csv";
$repo2="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$error="";
chdir($path);
$status ="Reinstalling scripts to current version";save_task_log ($status);
print"
$datum $status
";
$u = explode(",",$files);
foreach($u as $file) {
//if (!file_exists("$path/$file")){  // just reinstall them all. 
   print "sudo wget $repo2/$file
   "; 
 exec("sudo wget $repo2/$file ",$output,$return_var);
   }
 exec("sudo chmod +x *.php",$output,$return_var);
 


start("start");
}

function create_node ($file){
global $datum,$file,$path;
// phase 1 import node
$line= exec("cat /usr/local/etc/allstar_node_info.conf  |egrep 'NODE1='",$output,$return_var);
$line = str_replace('"', "", $line);
$u= explode("=",$line);
$node=$u[1];
$file= "$path/mm-node.txt"; $status ="Autoset node to $node";save_task_log ($status);
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node, , , , ");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);


// phase 2 import skywarn settings 
$file="/usr/local/bin/AUTOSKY/AutoSky.ini";
$file2="$path/autosky_import.ini";
copy($file, $file2);

}

 //
// $status ="what to log ";save_task_log ($status);print "$datum $status
//";
//
function save_task_log ($status){
global $path,$error,$datum,$file;

$datum  = date('m-d-Y H:i:s');
if(!is_dir("$path/logs/")){ mkdir("$path/logs/", 0755);}
chdir("$path/logs");
$file="$path/logs/log.txt";
$file2="$path/logs/log2.txt"; //if (file_exists($mmtaskTEMP)) {unlink ($mmtaskTEMP);} // Cleanup

// log rotation system
if (is_readable($file)) {
   $size= filesize($file);
   if ($size > 1000){
    if (file_exists($file2)) {unlink ($file2);}
    rename ($file, $file2);
    if (file_exists($file)) {print "error in log rotation";}
   }
}

$fileOUT = fopen($file, 'a+') ;
flock ($fileOUT, LOCK_EX );
fwrite ($fileOUT, "$datum,$status,,\r\n");
flock ($fileOUT, LOCK_UN );
fclose ($fileOUT);
}
