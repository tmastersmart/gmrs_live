  <?php
//  ------------------------------------------------------------
//  (c) 2023 by WRXB288 lagmrs.com all rights reserved
//
// Setup program
//
// -------------------------------------------------------------

// PHP is in UTC Get in sync with the PI

$ver="v1.8";

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

$logRotate = 30000;// size to rotate at

$in="";
$path         = "/etc/asterisk/local/mm-software";
$iax          = "/etc/asterisk/iax.conf";
$rpi          = "/etc/asterisk/rpt.conf";
$manager      = "/etc/asterisk/manager.conf";
$supermonPath = "/srv/http/supermon";
$allmon       = "$supermonPath/allmon.ini";   
$favorites    = "$supermonPath/favorites.ini";
$global       = "$supermonPath/global.inc";
$tmpFile      = "/tmp/temp.dat";
$logger       = "/etc/asterisk/logger.conf";

// automatic node setup

$file="";
create_node ($file);// testing create it everytime 

$file= "$path/mm-node.txt"; 
if(file_exists($file)){
$line = file_get_contents($file);
$u= explode(",",$line);
$AutoNode=$u[0];$call=$u[1];$autotts=$u[2];
}

include ("$path/check_reg.php");

reg_check ($in);$file="";


load($in);

$stripe="============================================================";
start_menu($in);
start("start");

function start($in){
global $call,$AutoNode,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$search,$fileEdit,$ok;

$stdin = fopen('php://stdin', 'r');
$yes   = false;

while (!$yes)
{
    $datum = date('m-d-Y-H:i:s');
	print "$datum Enter 1-2 E:_";
	$input = trim(fgets($stdin));
    if ($input == '1'){edit("edit");}
    if ($input == '2'){cronMenu("edit");}
    if ($input == '3'){speedtest("speed");}
    if ($input == 'f'){reg_force("force");} 
    if ($input == 's'){supermon("view");} 
    if ($input == 'd'){doc("view");}     
   	if ($input == 'u'){install("I");}
    if ($input == 'x'){uninstall("x");}  
	if ($input == 'e'){quit('EXIT');}
    if ($input == ''){quit('EXIT');}
}
}



function reg_force($in){
global $counter,$watchdog,$NotReg,$datum;

print"
Success rate for me is 100%. Your milage may vary. 
This is only for the following problem.
Your Router, Gateway, Modem or ISP Corrupts your open port so
it cant reach the reg server. 
I fix this by rotating the port# This forces a new path to the server.
Normaly registration is restored in a few seconds. 

After your back online the port will rotate back to default. On the next reboot.

Note: If you are using IAX client it wont be able to reconnect while on the alt port.

Watchdog can be set to run this fix automaticaly. 
";
reg_check ($in);
if($NotReg){reg_fix ("check");}
else {print"$datum This can only be run manualy if you are not registered.
"; }

start_menu($in);
}

function start_menu($in){
global $call,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;
global  $phpzone,$phpVersion; 
print "






$stripe
(c) 2023 by WRXB288 LAGMRS.com all rights reserved 
$phpzone PHP v$phpVersion
$stripe
Welcome $call  Made in Louisiana
  
 Setup.php  program 
          
 1) Edit Setup
 3) Speedtest
 F) Force Register Fix
 S) Supermon Setup
 D) View the Docs
 U) Upgrade (get the latest version)
 x) Uninstall This software.
 E) Exit  
$stripe
";
} 
 

function editmenu(){
global $tts,$sleep,$debug,$IconBlock,$forcast,$beta,$AutoNode,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate,$watchdog,$bridgeCheck;
$displayIconBlock="false";$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; 
$displayStatement="false";$displayReport="false";$displayForcast="false";$displayBeta="false";$displaydebug="false"; $displaySleep    ="false";$displaybridgeCheck= "false";
if ($sayWatch)    {$displayWatch =   "true";}
if ($sayWarn)     {$displayWarn     ="true";}
if ($sayAdvisory) {$displayAdvisory ="true";}
if ($sayStatement){$displayStatement="true";}
if ($forcast) {    $displayForcast  ="true";}
if ($beta){        $displayBeta     ="true";}
if ($reportAll)   {$displayReport   ="true";}
if ($IconBlock)   {$displayIconBlock="true";}
if ($debug)       {$displaydebug    ="true";}
if ($sleep)       {$displaySleep    ="true";}
if ($bridgeCheck) {$displaybridgeCheck= "true";}
print"

$stripe
 Setup editor.   Last write date:$saveDate

 1) Station [$station] Weather Station MADIS,APRSWXNET,Citizen Weather Observer Program (CWOP)
 2) Level Time [$level] 1=temp 2=cond 3=wind,hum,rain 4=Forcast (Levels to speak)
 3) Zipcode [$zipcode] for Acuweather conditions
 4) Location Lat:[$lat]/Lon:[$lon] needed for NWS API 
 5) SayWatch:$displayWatch SayAdvisory:$displayAdvisory SayStatement:$displayStatement 
 6) CPU Temp setup  HiTemp[$high]  Hot[$hot] All temps[$displayReport]
 7) Node   Auto:[$AutoNode]  Node:[$node]
 9) Beta features [$displayBeta]
 0) Watchdog limit. will fix reg automaticaly after [$watchdog] falures 99=disable
 d) Debugging info [$displaydebug]
 s) Dont say time when Sleeping 1-6 [$displaySleep]
 b) Bridging detection [$displaybridgeCheck]  
 c) Install into cron.
 u) Uninstall from cron. 
 W) Write to file.
 M) Main menu
 E) Exit  
$stripe
";

// i) Forcast Icon block on supermon page [$displayIconBlock]

}

function supermonMenu(){
global $forcast,$beta,$AutoNode,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate,$search,$fileEdit,$ok;
print"
$stripe
 Supermon installer and GMRS modifications: BETA
 
 If you already have it customized you may wish to wait for the next version
 this customizes the menus and adds favorites.  
 
     
 1) Setup supermon for first time. (dont use if already setup) 
  
 M) Main menu
 E) Exit  
$stripe
";
}



function speedtest($in){
  global $stripe,$path,$tmpFile;
  print "
$stripe  
Please wait for speedtest to run.......
Its slow, to run from command line type
speedtest-cli

";
  exec("speedtest-cli >$tmpFile",$output,$return_var);
  
$out = file_get_contents($tmpFile);
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


to test type

cd $path

php weather_pws.php
php temp.php
php cap_warn.php


this program is located in $path 
And can be run from the admin menu or by typing
php setup.php


* SLMR v2.1 * Have many nice days.
* 
* 
* ";
die;
}


function edit($in){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$bridgeCheck;
editmenu();
$stdin = fopen('php://stdin', 'r');
$yes   = false;
while (!$yes)
{
    $datum = date('m-d-Y-H:i:s');
	print "$datum Enter 1-9 M W E:_";
	$input = trim(fgets($stdin));
    if ($input == '1'){madis($station);}
    if ($input == '2'){level($level);}
    if ($input == '3'){zip($zipcode);}
    if ($input == '5'){warn($zipcode);}
    if ($input == '4'){location($lon);}
    if ($input == '6'){cpu($hot);}
    if ($input == '7'){setnode($node);}
 
    if ($input == '9'){beta("set");}
    if ($input == 'd'){debug("set");}
    if ($input == '0'){watchdog("set");}
    if ($input == 's'){sleep1("set");}
    if ($input == 'b'){bridge("set");}
    if ($input == 'c'){setUpcron("set");}
    if ($input == 'u'){unSetcron("set");}
    if ($input == 'm'){start_menu($in);start("start");}
    if ($input == 'w'){save("save");}
	if ($input == 'e'){quit('EXIT');}
}

}



// FM no static at all.  
function supermon($in){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;
supermonMenu();
$stdin = fopen('php://stdin', 'r');
$yes   = false;

while (!$yes)
{
    $datum = date('m-d-Y-H:i:s');
	print "$datum Enter 1-7 M E:_";
	$input = trim(fgets($stdin));
    if ($input == '1'){supermonGo("GO baby");}

    if ($input == 'm'){start_menu($in);start("start");}
    if ($input == 'w'){save("save");}
	if ($input == 'e'){quit('EXIT');}
}

}




// -------------------------------------------------------------supermon ----------------------------------------------
function supermonGo($in){
global $zipcode,$call,$password,$forcast,$beta,$AutoNode,$path,$node,$iax,$rpi,$manager,$supermonPath,$allmon,$favorites,$global,$tmpFile,$logger,$watchdog,$name,$location,$ip,$search,$fileEdit,$ok ;
print "
---------------------------------------------------------------
This will set up supermon for the first time to node: $node
It also adds GMRSLive links. Existing files will be backed up.

NOTICE: This is still in beta make backups of your CARD first!
        Just in case of bugs you can undo changes.....
        
        
---------------------------------------------------------------
        

  i) install 

 Any other key to abort 
";
$a = readline('Enter your command: ');
if ($a=="i"){
// first 
print "[Entering return anytime will abort]
";
save_task_log ("Installing Supermon");

print"
The supermon page will display this info its not used anywhere else.
=================================================================
";
$name = readline('Your Name for supermon page: ');
$location = readline('Your Location for supermon page: ');
print "Zipcode = $zipcode
=================================================================
";
$password = getRandomString(5);
print " Using random password of $password on IAX connections.
";

$manager= "/etc/asterisk/manager.conf";
$fileEdit=$manager;$search= "secret ="; edit_config("secret =$password"); 

print "Password $password entered in $manager\n";
buildAllmon($node);
print "Password entered in $allmon  with custom links to GMRSLive\n";

chdir("$supermonPath");
$file="$supermonPath/.htpasswd";
if (file_exists($file)){ unlink($file); }


print "Admin password  Username:admin \n";
exec ("htpasswd -cB .htpasswd admin",$output,$return_var);


print "Building global.ini  $call \n";
buildGlobal($node); 
$rpi="/etc/asterisk/rpt.conf"; // debugging....Need to be sure which file we are editing
$fileEdit=$rpi;$search="connpgm=/usr/";edit_config("connpgm=/usr/local/sbin/supermon/smlogger 1");
$fileEdit=$rpi;$search="discpgm=/usr/";edit_config("discpgm=/usr/local/sbin/supermon/smlogger 0"); 

$logger="/etc/asterisk/logger.conf";
$fileEdit=$logger;$search= "messages =>"; edit_config("messages => notice,warning,error,verbose");

print "adding trimlog to cron\n";
$in="10,25,40,55 * * * * /usr/local/sbin/trimlog.sh /var/log/asterisk/messages 1000";$search="trimlog.sh";cronAdd($in);



$file="/srv/http/index.html";if (file_exists($file)) {unlink ($file);}
$file="/srv/http/index.php" ;if (file_exists($file)) {unlink ($file);}






print"Requesting astres.sh restart\n";
exec ("astres.sh",$output,$return_var);  // Run restart


// install redirect
$file="/srv/http/index.php" ;
$fileOUT = fopen($file, "w");$out = "<?php
header('Location: /supermon/link.php?nodes=$node');
die();
?>";    // <?php
fwrite ($fileOUT, $out);fclose ($fileOUT);


print"Your supermon is setup visit the url with a web browser $ip\n";
}


start_menu($in);start("start");
}


function bridge($in){
global $bridgeCheck;

$displaybridgeCheck="false";
if ($bridgeCheck) {   $displaybridgeCheck ="true";}
print "
Automatic bridging detection and auto disconnect [$displaybridgeCheck]
Check is run on a schedule if bridging is detected a message will 
play warning you and a disconnect will be atempted.

Still in beta so if you see any problems just turn it off.
";
$line = readline("Set to t/f: ");
if ($line=="t"){$bridgeCheck=true;}
if ($line=="f"){$bridgeCheck=false;}
editmenu();
}




function sleep1(){
global $sleep ;

$displaySleep="false";if ($sleep){$displaySleep="true";}
print "
Speep mode [$displayBeta]
When in sleep mode will not talk between 1 and 6. 
Allows script to run ever hr but not function at night  
";
$line = readline("Set to t/f: ");
if ($line=="t"){$sleep=true;}
if ($line=="f"){$sleep=false;}
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

function debug($in){
global $debug,$forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayDebug="false";
if ($debug){        $displayDebug     ="true";}
print "
Debugging gives extra info on to the console. On the API will will print the log forcast to the console.
Debug is set to $displayDebug
";
$line = readline("Set to t/f: ");
if ($line=="t"){$debug=true;}
if ($line=="f"){$debug=false;}
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
1=temp,2=cond 3=wind,hum,rain 4=Forcast  (Levels to speak)

";
$line = readline("Level $level: ");
$level = $line;
editmenu();
}

function zip($level){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false";
print "
 Acuweather gives us conditions, It needs a zipcode [$zipcode] 
";
$line = readline("ZipCode $zipcode: ");
$zipcode = $line;
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



function watchdog($in){
global $watchdog;

print "
Watchdog timmer.  On this many falures [$watchdog] take action.

In case you get Not Registered abd it wont come back online it will rotate
the port to a random port and restart ast. This solves my ATT Fixed Wireless problem 

Which is designed to bypasses a port block on your modem, router or ISP.

3 or 4 recomended 

If you are having problems staying registered after a few days you can try this. 
If it doesnt work o well its something to try works for me....

Set to 99 to disable.   
";
$line = readline("Watchdog timmer $watchdog: ");
$watchdog = $line;
if($watchdog==""){$watchdog=2;}
editmenu();
}




function doc($in){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate,$stripe;
$i=0;
$file="$path/readme.txt";
print "
 Loading the readme.txt file
$stripe
";
$fileIN= file($file);
foreach($fileIN as $line){
print $line; $i++ ;
if ($i >42){$line = readline(" ");$i=0;}
}
print "$stripe
End of File
";
$line = readline("Hit Return for menu");
start_menu($in);start("start");
}

function warn($skywarn){
global $forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; $displayStatement="false";$displayReport="false"; 
if ($sayWatch)    {$displayWatch =   "true";}
if ($sayWarn)     {$displayWarn     ="true";}
if ($sayAdvisory) {$displayAdvisory ="true";}
if ($sayStatement){$displayStatement="true";}

print "
Cap Warn uses Common Alerting Protocol (CAP) from the NWS
The new API needs your LAT/LON  [$lat/$lon]


Customise what reports you want
SayWarning   true <hard coded>
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




function load($in){
global $bridgeCheck,$autotts,$tts,$sleep,$IconBlock,$debug,$AutoNode,$datum,$forcast,$beta,$saveDate,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$high,$hot,$nodeName,$reportAll,$watchdog;

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
         $IconBlock =  $u[5];
               $lat =  $u[6];
               $lon =  $u[7];
           $sayWarn =  $u[8];
          $sayWatch =  $u[9];
       $sayAdvisory = $u[10];
      $sayStatement = $u[11];
        $sleep      = $u[12];
              $high = $u[13]; 
               $hot = $u[14];
          $nodeName = $u[15];
         $reportAll = $u[16];
          $saveDate = $u[17];
           $forcast = $u[18]; // not using
              $beta = $u[19];
          $watchdog = $u[20];
             $debug = $u[21];
               $tts = $u[22]; // later add on
       $bridgeCheck = $u[23];     
    }
}



else {
// set default
$status ="Building default settings";save_task_log ($status);
print "$datum $status
";
$reportAll = false;$nodeName = "server";$high=60;$hot=50;
// https://madis-data.ncep.noaa.gov/MadisSurface/
$station="KAEX";$level = 3 ;  // Alexandria International Airport (AEX)
$zipcode="71432"; $IconBlock= true; $skywarn ="";
$lat ="31.3273"; $lon="-92.5485"; // Alexandria International Airport	31.3273717,-92.5485558
$sleep=true;$tts=$autotts;
$sayWarn=true;$sayWatch=true;$sayAdvisory=true;$sayStatement =true;
$node = $AutoNode;$forcast= false;$beta = false; $debug=false;
$watchdog = 10;
$bridgeCheck = true;
save ("save");
}
}


function save($in){
global $tts,$debug,$datum,$forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$IconBlock,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$sleep,$high,$hot,$nodeName,$reportAll,$watchdog,$bridgeCheck;
$fileOUT = fopen("$path/setup.txt", "w");
$status ="Writing settings";save_task_log ($status);
print "$datum $status
";
fwrite ($fileOUT, "$path,$node,$station,$level,$zipcode,$IconBlock,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$sleep,$high,$hot,$nodeName,$reportAll,$datum,$forcast,$beta,$watchdog,$debug,$tts,$bridgeCheck,,,,");
fclose ($fileOUT);
}

function uninstall($in){
global $datum,$file,$path;

print "
---------------------------------------------------------------
This will remove all the software 
---------------------------------------------------------------
Some setting files may remain.

        

  u) uninstall 

 Any other key to abort 
";
$a = readline('Enter your command: ');

if ($a == "u"){

$files = "bridged.gsm,clear.wav,heat_advisory.wav,flood_advisory.wav,weather_service.wav,hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";
$path  = "/etc/asterisk/local/mm-software";
$path2 = "/etc/asterisk/local/mm-software/sounds";

chdir($path2);
$datum = date('m-d-Y-H:i:s');
print"$datum Uninstalling sounds\n";

foreach (glob("*.gsm") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 

foreach (glob("*.wav") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
foreach (glob("*.ul") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
    
    
    

$files = "supermon.txt,readme.txt,sound_wav_db.csv,sound_gsm_db.csv,sound_ulaw_db.csv,supermon_weather.php,load.php,forcast.php,temp.php,cap_warn.php,weather_pws.php,sound_db.php,check_reg.php,nodelist_process.php,check_gmrs.sh,sound_db.php";
$error = "";
$path  = "/etc/asterisk/local/mm-software";
chdir($path);
$datum = date('m-d-Y-H:i:s');
print"$datum Uninstalling php files\n";

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
    
foreach (glob("*.sh") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 






print "$datum setup.php is running and can not be deleted.";

$file = "$path/setup.txt";    if (file_exists($file)){unlink ($file);} 
$file = "$path/mm-node.txt";  if (file_exists($file)){unlink ($file);} 
$file = "$path/logs/log.txt"; if (file_exists($file)){unlink ($file);}
$file = "$path/logs/log2.txt";if (file_exists($file)){unlink ($file);}

$file = "$path/allstar_node_info.conf";if (file_exists($file)){unlink ($file);}



$file = "$path/nodelist/clean.csv";    if (file_exists($file)){unlink ($file);}
$file = "$path/nodelist/dirty.csv";    if (file_exists($file)){unlink ($file);}
$file = "$path/nodelist/hubs.csv";     if (file_exists($file)){unlink ($file);}
$file = "$path/nodelist/repeaters.csv";if (file_exists($file)){unlink ($file);}

$file ="/usr/local/sbin/firsttime/mmsoftware.sh";if (file_exists($file)){unlink ($file);}


//$file = "/srv/http/supermon/links.php";    if (file_exists($file)){unlink ($file);}
$file = "/srv/http/supermon/gmrs-rep.php"; if (file_exists($file)){unlink ($file);}
$file = "/srv/http/supermon/gmrs-hubs.php";if (file_exists($file)){unlink ($file);}
$file = "/srv/http/supermon/gmrs-list.php";if (file_exists($file)){unlink ($file);}




unSetcron($in);




} 
quit('EXIT');
}





function install($in){
global $datum,$file,$path;

print "
---------------------------------------------------------------
This will update to the software to the current version
---------------------------------------------------------------
        

  u) update 

 Any other key to abort 
";
$a = readline('Enter your command: ');

if ($a == "u"){
$files = "bridged.gsm,clear.wav,heat_advisory.wav,flood_advisory.wav,weather_service.wav,hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul";

$path  = "/etc/asterisk/local/mm-software";
$path2 = "$path/sounds";


if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2);

$u = explode(",",$files);

$repo="https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/sounds";
$datum = date('m-d-Y-H:i:s');
print"$datum Checking for missing sounds\n";

foreach($u as $file) {
if (!file_exists("$path2/$file")){
  print "downloading $file\n";
  exec("sudo wget $repo/$file",$output,$return_var);
  }
}
// install other  - setup.php is running so download to temp file - setup.php,
$files = "supermon_weather.php,load.php,forcast.php,temp.php,cap_warn.php,weather_pws.php,sound_db.php,check_reg.php,nodelist_process.php,check_gmrs.sh,sound_db.php";
$repo = "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$error = "";

chdir($path);
$status ="Reinstalling scripts to current version";save_task_log ($status);print"$datum $status\n";

$u = explode(",",$files);
foreach($u as $file) {
  print "downloading $file\n";
  if (file_exists($file)){unlink($file);}
  exec("sudo wget $repo/$file",$output,$return_var);
  exec("sudo chmod +x $file",$output,$return_var); 
 }

chdir($path);
// non chmod files to install
$files = "supermon.txt,readme.txt,sound_wav_db.csv,sound_gsm_db.csv,sound_ulaw_db.csv";
$u = explode(",",$files);
foreach($u as $file) {
 print "downloading $file\n";
 if (file_exists($file)){unlink($file);} 
 exec("sudo wget $repo/$file ",$output,$return_var);
 }

 
// Install the supermon mods
chdir("/srv/http/supermon");
$repo = "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/supermon";
$files = "links.php,gmrs-rep.php,gmrs-hubs.php,gmrs-list.php";
$u = explode(",",$files);
foreach($u as $file) {
 print "downloading $file\n"; 
 if (file_exists($file)){unlink($file);}
 exec("sudo wget $repo/$file ",$output,$return_var);
} 
 
 
 
 

// special download of setup because its running. Will install elsewhere 
$repo = "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/supermon";
$path2 = "$path/update";
if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2); 
 print "downloading $file\n"; 
 exec("sudo wget $repo/setup.php ",$output,$return_var);
 exec("sudo chmod +x setup.php",$output,$return_var); 

// make backups
$cur   = date('mdyhis');
$file = "$path/setup.txt";  $file2= "$path2/setup-$cur.txt";  copy($file, $file2);
$file = "$path/mm-node.txt";$file2= "$path2/mm-node-$cur.txt";copy($file, $file2);
$file = "$path/logs/log.txt";$file2= "$path2/log-$cur.txt"   ;copy($file, $file2);


} 

start_menu("start");start("start");
}

function create_node ($file){
global $file,$path;
// phase 1 import node - call
//$line= exec("cat /usr/local/etc/allstar_node_info.conf  |egrep 'NODE1='",$output,$return_var);
$file ="/usr/local/etc/allstar_node_info.conf"; copy($file, "$path/allstar_node_info.conf");
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

// /usr/local/etc/tts.conf 
// Get any tss key if exists
$file ="/usr/local/etc/tts.conf";
$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$line = str_replace('"', "", $line);
$pos = strpos("-$line", 'tts_key=');
if ($pos){$u= explode("=",$line);
$tts=$u[1];}
}

$file= "$path/mm-node.txt";// This will be the AutoNode varable
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node,$call,$tts, , ,");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

//save_task_log ("Imported node:$node Call:$call");
// phase 2 import skywarn settings 
//$file="/usr/local/bin/AUTOSKY/AutoSky.ini";
//$file2="$path/autosky_import.ini";
//copy($file, $file2);

}

 //
// $status ="what to log ";save_task_log ($status);print "$datum $status
//";
//
function save_task_log ($status){
global $path,$error,$datum,$logRotate;

$datum  = date('m-d-Y H:i:s');
if(!is_dir("$path/logs/")){ mkdir("$path/logs/", 0755);}
chdir("$path/logs");
$log="$path/logs/log.txt";
$log2="$path/logs/log2.txt"; //if (file_exists($mmtaskTEMP)) {unlink ($mmtaskTEMP);} // Cleanup

// log rotation system
if (is_readable($log)) {
   $size= filesize($log);
   if ($size > $logRotate){
    if (file_exists($log2)) {unlink ($log2);}
    rename ($file, $log2);
    if (file_exists($log)) {print "error in log rotation";}
   }
}

$fileOUT = fopen($log, 'a+') ;
flock ($fileOUT, LOCK_EX );
fwrite ($fileOUT, "$datum,$status,,\r\n");
flock ($fileOUT, LOCK_UN );
fclose ($fileOUT);
}





//this is my own editor to search and replace
//
function edit_config($in){ 
global $search,$path,$fileEdit,$ok;
print "Edit file:$fileEdit Search:$search \n";
$ok=false;$line="";
if (file_exists($fileEdit)){

$fileBu = "$fileEdit-.bak"; if (file_exists($fileBu)){ unlink($fileBu); }
copy($fileEdit,$fileBu);
if(!file_exists($fileBu)){ print "Unable to make a BackUP.";}

$tmpFile="$fileEdit-new.txt"; // keep in the same dir so we wont have to copy

$fileIN= file($fileEdit);
$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", $search); if ($pos>=1){print"$line Replacing with $in \n"; $line=$in;$ok=true;}  // Replace the found line with a new one.
fwrite ($fileOUT, "$line\n");
}

fclose ($fileOUT);
// if ok then we found it
if ($ok){
if (file_exists($tmpFile)){ unlink($fileEdit);} 
rename ($tmpFile, $fileEdit); save_task_log ("$fileEdit edited $in");

   } // rename
else{print "ERROR $in not found in $fileEdit\n";}   
  } //file exist

// try to prevent stray data
$fileEdit="";$search="";
}





// global.inc'
function buildGlobal($in){
global $zipcode,$global,$path,$file,$tmpFile,$ok,$password,$node,$call,$name,$location;

$file = $global;

if (file_exists($file)){
$fileBu = "$file-.bak"; if (file_exists($fileBu)){ unlink($fileBu); }
copy($file,$fileBu);if(!file_exists($fileBu)){ print "Unable to make a BackUP.\n";}

$fileOUT = fopen($global, "w") or die ("Error $global Write falure\n");  // 


$formated="
<?php
#CALL = '$call';
#NAME = '$name';
#LOCATION = '$location';
#TITLE2 = 'GMRS Live Node ';
#TITLE3 = 'System Node Manager';
#BACKGROUND = 'background.jpg';
#BACKGROUND_COLOR = 'blue';
#BACKGROUND_HEIGHT = '124px';
#REFRESH_DELAY = '21600';
#SHOW_COREDUMPS = 'yes';
#LOCALZIP = '$zipcode';
#MAINTAINER = '';
?>
";//  <?php
$formated = str_replace("'", '"', $formated);
$formated = str_replace('#', '$', $formated);

fwrite ($fileOUT, $formated);
fclose ($fileOUT);

print "saving $global \n";


save_task_log ("$file saved");

}
}
 
function buldFav($in){
global $favorites,$path,$file,$tmpFile,$ok,$password,$node;
$favorites    = "$supermonPath/favorites.ini";
$file = $favorites;

if (file_exists($file)){
$fileBu = "$file-.bak"; if (file_exists($fileBu)){ unlink($fileBu); }
copy($file,$fileBu);if(!file_exists($fileBu)){ print "Unable to make a BackUP.";}

$fileOUT = fopen($file, "w") or die ("Error $file Write falure\n");  // 

$formated="
[general]

label[] = 'RoadKill 1195'
cmd[] = 'rpt cmd %node% ilink 3 1195'

label[] = 'RoadKill DV Switch 1167'
cmd[] = 'rpt cmd %node% ilink 3 1167'

label[] = 'Texas GMRS Network 2250'
cmd[] = 'rpt cmd %node% ilink 3 2250'

label[] = 'Nationwide Chat 700'
cmd[] = 'rpt cmd %node% ilink 3 700'

label[] = 'Repair/Tuneup 611'
cmd[] = 'rpt cmd %node% ilink 3 611'

label[] = 'The Lone Wolf 1691'
cmd[] = 'rpt cmd %node% ilink 3 1691'

label[] = 'ALAMO CITY 1510'
cmd[] = 'rpt cmd %node% ilink 3 1510'

label[] = 'CENTRAL ILLINOIS 1915'
cmd[] = 'rpt cmd %node% ilink 3 1915'

label[] = 'EOC Emergency Ops 900'
cmd[] = 'rpt cmd %node% ilink 3 900'

label[] = 'Edit Favorties.ini to add content'
cmd[] = 'NONE'
";

$formated = str_replace("'", '"', $formated);
fwrite ($fileOUT, $formated);
fclose ($fileOUT);
print "saving $allmon \n";
save_task_log ("$file saved");
}
}






function buildAllmon($in){
global $allmon,$path,$file,$tmpFile,$ok,$password,$node;

$file = $allmon;

if (file_exists($file)){
$fileBu = "$file-.bak"; if (file_exists($fileBu)){ unlink($fileBu); }
copy($file,$fileBu);if(!file_exists($fileBu)){ print "Unable to make a BackUP.";}

$fileOUT = fopen($file, "w") or die ("Error $file Write falure\n");  // 

$formated="
[$node]
host=127.0.0.1:5038
user=admin
passwd=$password
menu=yes
system=Nodes
hideNodeURL=no

[All Nodes]
sstem=Display Groups
nodes=$node
menu=yes

[Repeators]
url='/supermon/gmrs-rep.php'
menu=yes

[Hubs]
url='/supermon/gmrs-hubs.php'
menu=yes

[lsNodes]
url='/cgi-bin/lsnodes_web?node=$node'
menu=yes

[GMRSLive]
url='http://gmrslive.com/status/link.php?nodes=700,900'
menu=yes

[RoadKill]
url='http://1195.node.gmrslive.com/link.php?nodes=1195,1196,1167'
menu=yes

[Texas]
url='https://link.texasgmrs.net/link.php?nodes=2250,2251,2252,2253,2254,1000,922'
menu=yes

[Alamo City]
url='http://1510.node.gmrslive.com/supermon/link.php?nodes=1510,1512,1513,1680,1684'
menu=yes

[Florida]
url='http://www.lonewolfsystem.org/supermon/link.php?nodes=1691'
menu=yes

[Broadnet NY]
url='https://statusbroadnetgmrs.net/link.php?nodes=1420,1428,1430,1431,1432,1433,1434,2107,2108,2109,921'
menu=yes

[Cen IL]
url='http://1915.node.gmrslive.com/supermon/link.php?nodes=1915'
menu=yes 

[Ottawa Lake MI]
url='http://1171.node.gmrslive.com/supermon/link.php?nodes=1114'
menu=yes

[Southwest MI]
url='http://1082.node.gmrslive.com/supermon/link.php?nodes=1082'
menu=yes

[Facebook]
url='https://www.facebook.com/groups/gmrslive'
menu=yes
";
$formated = str_replace("'", '"', $formated);
fwrite ($fileOUT, $formated);
fclose ($fileOUT);
print "saving $allmon \n";
save_task_log ("$file saved");
}
}

function getRandomString($n){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    save_task_log ("IAX password $randomString");
    return $randomString;
    
}


// cronDel($in) cronAdd($in)  $search
// --------------------------------ADD
function cronAdd ($in){
global $path,$search;
$file    = "$path/cron.txt";
$tmpFile = "$path/cron-new.txt"; if (file_exists($tmpFile)){ unlink($tmpFile); }
exec ("crontab -l > $file",$output,$return_var);
$ok=false;$dupe=false;
$fileIN= file($file);
$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);

$pos = strpos("-$line", $search);if ($pos>=1){ $ok=true;}// dupe detection
fwrite ($fileOUT, "$line\n");
}
  
if($ok){fwrite ($fileOUT, "$in\n");} // Its ok to add so write it
}
fclose ($fileOUT);


if ($ok){ 
exec ("crontab $tmpFile",$output,$return_var);
$status ="Add to cron ok\n";save_task_log ($status);
}
else{
print"Already in cron\n";
save_task_log ("Skipping Add to chron $in");
}
 
if (file_exists($tmpFile)){ unlink($tmpFile);
}

//-------------------------------DEL
function cronDel($in){
global $search,$path;
$file    = "$path/cron.txt";
$tmpFile = "$path/cron-new.txt"; if (file_exists($tmpFile)){ unlink($tmpFile); }
exec ("crontab -l > $file",$output,$return_var);
$ok=false;
$fileIN= file($file);
$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");
foreach($fileIN as $line){

$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", $search); 
if ($pos){$ok=true;print"-
";}  
else{fwrite ($fileOUT, "$line\n");print "$line
";}
}
fclose ($fileOUT);

if ($ok){
exec ("crontab $tmpFile",$output,$return_var);
$status ="Del from cron $in";save_task_log ($status);
}
else{save_task_log ("Del from chron (not found) $in");}

}
// remove from chron
function unSetcron($in){
global $search,$node1,$path;

 
// install the backup

$file = "$path/cron-orginal.txt";
if (file_exists($file)){exec ("crontab $tmpFile",$output,$return_var);}
else{
print"No backup exist installing a default
";
$replace="# Do not remove the following line
# required for lsnodes and allmon
15 03 * * * cd /usr/local/sbin; ./astdb.php cron
00 0-23 * * * (source /usr/local/etc/allstar.env ; /usr/bin/nice -19 /usr/bin/perl /usr/local/sbin/saytime.pl \$NODE1 > /dev/null)
";
$tmpFile = "$path/cron-new.txt"; if (file_exists($tmpFile)){ unlink($tmpFile); }
$fileOUT = fopen($tmpFile, "w");fwrite ($fileOUT, $replace);fclose ($fileOUT);
exec ("crontab $tmpFile",$output,$return_var);
print "$replace
";
}
 }

// sets up cron and removes old scripts.
function setUpcron($in){
global $search;

// make a backup for uninstall
$file = "$path/cron-orginal.txt";exec ("crontab -l > $file",$output,$return_var);

// comment out existing time string. 
//#00 8-23 * * * (source /usr/local/etc/allstar.env ; /usr/bin/nice -19 /usr/bin/perl /usr/local/sbin/saytime.p$
$search="/usr/local/sbin/saytime";$in="";cronDel($in);
$search="AutoSky";$in="";cronDel($in);

//@hourly php /etc/asterisk/local/mm-software/weather_pws.php > /dev/null

$search="weather_pws.php";$in="00 * * * * php /etc/asterisk/local/mm-software/weather_pws.php >/dev/null";cronAdd($in);
$search="cap_warn.php"   ;$in="*/15 * * * * php /etc/asterisk/local/mm-software/cap_warn.php >/dev/null";cronAdd($in);
$search="temp.php"       ;$in="*/20 * * * * php /etc/asterisk/local/mm-software/temp.php >/dev/null"; cronAdd($in) ;

//
//stop the looping script from being in memory

$fileEdit="/etc/rc.local"; $search="AutoSky";edit_config("#");

 
}
// Merge in the changes 
function modifyWEB ($in){
global $search,$searchEnd,$file,$path,$tmpFile,$ok;
$ok=false;$start=false;$end=false;
if (file_exists($file)){

$fileBu = "$file-.bak"; if (file_exists($fileBu)){ unlink($fileBu); }
copy($file,$fileBu);if(!file_exists($fileBu)){ print "Unable to make a BackUP.";}

$fileIN= file($file);
$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");
foreach($fileIN as $line){

$pos  = strpos("-$line", $search);
$pos2 = strpos("-$line", $searchEnd);
if ($pos  >= 1){$start=true;}
if ($pos2 >= 1){$end  =true;} 
if ($start){fwrite ($fileOUT, $in);} // insert a block


if (!$start){}

if (!$end){fwrite ($fileOUT, $line);}

} 
 
fwrite ($fileOUT, "$line\n");
}

fclose ($fileOUT);
if ($ok){
if (file_exists($tmpFile)){ unlink($file); 
 if (!file_exists($file)){ 
 rename ($tmpFile, $file);
 save_task_log ("$file edited $in");
 }
 else{ print "ERROR can not unlink file $file
 ";
 $ok=false;save_task_log ("$file failed edit");}
   } // rename
  } // ok
 } //file exist





?>
