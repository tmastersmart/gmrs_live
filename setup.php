<?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
// Config program
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
$u= explode(",",$line);$node=$u[0];
}
if (!$node){
$datum = date('m-d-Y-H:i:s');
print"$datum Error loading node number";die;}
}
$stripe="============================================================";
start("start");

function start($in){
global $stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;

global  $phpzone,$phpVersion; 
  load($in);
print "

    ___             __ _       
  / __\___  _ __  / _(_) __ _ 
 / /  / _ \| '_ \| |_| |/ _` |
/ /__| (_) | | | |  _| | (_| |
\____/\___/|_| |_|_| |_|\__, |
                        |___/           


(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
$phpzone PHP v$phpVersion
$stripe
Welcome 
  
 Config program will set up the program 
          
 1) Edit Setup 
 2) 
 3) Upgrade
 
 E) Exit  
$stripe
";

//$a = readline('1 2 3 :>');


$stdin = fopen('php://stdin', 'r');
$yes   = false;

while (!$yes)
{
    $datum = date('m-d-Y-H:i:s');
	print "$datum Enter :_";
	$input = trim(fgets($stdin));
    if ($input == '1'){edit("edit");}
//    if ($input == '2'){exit;}
//   	if ($input == '3'){install($out);} 
//    if ($input == '4'){exit('EXIT');}
	if ($input == 'e'){quit('EXIT');}
}
}


function quit($in){

print "
$stripe
Thank you for downloading.........



* SLMR v2.1 * Have many nice days.
";
die;
}

function guidedSetup($in){
global $node,$path;
}
function edit($in){
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;

editmenu();


$stdin = fopen('php://stdin', 'r');
$yes   = false;

while (!$yes)
{
    $datum = date('m-d-Y-H:i:s');
	print "$datum Enter :_";
	$input = trim(fgets($stdin));
    if ($input == '1'){madis($station);}
    if ($input == '2'){level($level);}
    if ($input == '3'){zip($zipcode);}
    if ($input == '4'){warn($zipcode);}
    if ($input == '5'){location($lon);}
    if ($input == '6'){cpu($hot);}
    if ($input == 'm'){start("start");}
    if ($input == 'w'){save("save");}
	if ($input == 'e'){quit('EXIT');}
}

}

function madis($station){
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
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
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
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
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";
print "
 Acuweather gives us conditions, It needs a zipcode [$zipcode] 
";
$line = readline("ZipCode $zipcode: ");
$station = $line;
editmenu();
}

function cpu($level){
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
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
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;

print "
 Location for new NWS api  [$lat]/[$lon] 
";
$line = readline("Lat $lat: ");
$lat = $line;
$line = readline("Lon $lon: ");
$lon = $line;


editmenu();
}
function warn($skywarn){
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
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
global $stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";
if ($sayWatch)    {$displayWatch =   "true";}
if ($sayWarn)     {$displayWarn     ="true";}
if ($sayAdvisory) {$displayAdvisory ="true";}
if ($sayStatement){$displayStatement="true";}

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

 W) Write to file.
 M) Main menu
 E) Exit  
$stripe
";



}

function load($in){
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;

// read setup file  30 data points
if (is_readable("$path/setup.txt")) {
   $fileIN= file("$path/setup.txt");
   $datum = date('m-d-Y-H:i:s');
print "$datum Loading settings
";
   foreach($fileIN as $line){
    $u = explode(",",$line);
//            $path =  $u[0];
//           $node  =  $u[1];
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
        $reportAll  = $u[16];
        $saveDate   = $u[17];
    }
}
else {
// set default
$datum = date('m-d-Y-H:i:s');
print "$datum Building default settings
";
$reportAll = false;
$nodeName = "server";
$high = 60;
$hot  = 50;
$station="KIER";
$level = 3 ;
$zipcode="71432";
$skywarn    ="LAC043";
$lat ="31.7669"; $lon="-92.3888";
$sayWarn=true;
$sayWatch=true;
$sayAdvisory=true;
$sayStatement =true;
save ("save");
}
}


function save($in){
global $path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;
$fileOUT = fopen("$path/setup.txt", "w");
   $datum = date('m-d-Y-H:i:s');
print "$datum Writing settings
";
fwrite ($fileOUT, "$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$datum,,,,,,");
fclose ($fileOUT);
}


function install($in){

$files = "clear.wav,flood_advisory.wav,weather_service.wav,hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";
$path  = "/etc/asterisk/local/mm-software";// moved to special dir for dist.
$path2 = "$path/sounds";

$u = explode(",",$files);
if(!is_dir($path)){ mkdir($path, 0755);}
chdir($path);
if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2);
$repo="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/sounds";
$datum = date('m-d-Y-H:i:s');
print"
$datum Install sounds
";

foreach($u as $file) {
if (!file_exists("$path2/$file")){
   print "sudo wget $repo/$file
   "; 
exec("sudo wget $repo/$file",$output,$return_var);
   }
   }
// install other
$files = "config.php,temp.php,weather_pws.php,check_gmrs.sh,sound_wav_db.csv,sound_gsm_db.csv,skywarn.php";
$repo2="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$error="";
chdir($path);
$datum = date('m-d-Y-H:i:s');
print"
$datum Installing scripts
";
$u = explode(",",$files);
foreach($u as $file) {
if (!file_exists("$path/$file")){ 
   print "sudo wget $repo2/$file
   "; 
 exec("sudo wget $repo2/$file ",$output,$return_var);
   }
   }
}

function create_node ($file){
global $file,$path;
// phase 1 import node
$line= exec("cat /usr/local/etc/allstar_node_info.conf  |egrep 'NODE1='",$output,$return_var);
$line = str_replace('"', "", $line);
$u= explode("=",$line);
$node=$u[1];
$file= "$path/mm-node.txt";
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node, , , , ");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);


// phase 2 import skywarn settings 
$file="/usr/local/bin/AUTOSKY/AutoSky.ini";
$file2="$path/autosky_import.ini";
copy($file, $file2);

}
