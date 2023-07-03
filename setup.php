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

$logRotate = 30000;// size to rotate at
$ver="v1.2";
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

$u= explode(",",$line);$AutoNode=$u[0];$call=$u[1];
}

include ("$path/check_reg.php");

reg_check ($in);

load($in);

$stripe="============================================================";
start_menu($in);
start("start");

function start($in){
global $call,$AutoNode,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;

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
   	if ($input == 'u'){install($out);} 
	if ($input == 'e'){quit('EXIT');}
    if ($input == ''){quit('EXIT');}
}
}



function reg_force($in){
global $counter,$watchdog,$NotReg,$datum;

print"
Success rate for me is 100%. Your milage may vary. 
This works if your Router, Gateway, Modem or ISP Corrupts your open port so it cant reach the reg server.
I fix this by rotating the port# This forces a new path to the server.
Normaly registration is restored in a few seconds. 
After your back online the port will rotate back to default. On the next reboot.

Note: If you are using IAX client it wont be able to reconnect. 
";
reg_check ($in);
//if($NotReg){
reg_fix ("check");
//}
//else {print"$datum This can only be run manualy if you are not registered.
//"; }
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
Welcome $call
  
 Setup.php  program 
          
 1) Edit Setup
 3) Speedtest
 F) Force Register Fix
 S) Supermon Setup
 D) View the Docs
 U) Upgrade (get the latest version)
 E) Exit  
$stripe
";
} 
 

function editmenu(){
global $sleep,$debug,$IconBlock,$forcast,$beta,$AutoNode,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate,$watchdog;
$displayIconBlock="false";$displayWatch="false"; $displayWarn="false";$displayAdvisory="false"; 
$displayStatement="false";$displayReport="false";$displayForcast="false";$displayBeta="false";$displaydebug="false"; $displaySleep    ="false";
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
print"

$stripe
 Setup editor.   Last write date:$saveDate
      
 1) Station [$station] (MADIS/CWOP) Station  
 2) Level Time [$level] 1=temp 2=cond 3=wind,hum,rain 4=Forcast (Levels to speak)
 3) Zipcode [$zipcode] for Acuweather conditions
 4) Location Lat:[$lat]/Lon:[$lon] needed for NWS API 
 5) SayWatch:$displayWatch SayAdvisory:$displayAdvisory SayStatement:$displayStatement 
 6) CPU Temp setup  HiTemp[$high]  Hot[$hot] All temps[$displayReport]
 7) Node   Auto:[$AutoNode]  Node:[$node]
 8) Forcast [$displayForcast] 
 9) Beta features [$displayBeta]
 0) Watchdog limit [$watchdog]
 d) Debugging info [$displaydebug]
 s) Dont say time when Sleeping 1-6 [$displaySleep]
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
global $forcast,$beta,$AutoNode,$stripe,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$saveDate;
print"
$stripe
 Supermon installer and GMRS modifications:
     
 1) Setup supermon for first time. 
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



* SLMR v2.1 * Have many nice days.
* 
* 
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
	print "$datum Enter 1-9 M W E:_";
	$input = trim(fgets($stdin));
    if ($input == '1'){madis($station);}
    if ($input == '2'){level($level);}
    if ($input == '3'){zip($zipcode);}
    if ($input == '5'){warn($zipcode);}
    if ($input == '4'){location($lon);}
    if ($input == '6'){cpu($hot);}
    if ($input == '7'){setnode($node);}
    if ($input == '8'){forcast("set");}
    if ($input == '9'){beta("set");}
    if ($input == 'd'){debug("set");}
    if ($input == '0'){watchdog("set");}
    if ($input == 's'){sleep("set");}
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
global $call,$password,$forcast,$beta,$AutoNode,$path,$node,$iax,$rpi,$manager,$supermonPath,$allmon,$favorites,$global,$tmpFile,$logger,$watchdog ;
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
print "[Entering return anytime will abort]";
save_task_log ("Installing Supermon");
$password = getRandomString(10);
print " Using random password of $password on IAX connections.";
// /etc/asterisk/manager.conf
$file=$manager;$search= "secret ="; $in="secret =$password";
edit_config($in); 
print " Password entered in $manager  
";
buildAllmon($node);
print " Password entered in $allmon  with custom links to GMRSLive
";

$line = readline("Enter Password for Supermon: ");
if ($line==""){start('go');}

chdir("$supermonPath"); $file="$supermonPath/.htpasswd";
if (file_exists($file)){ unlink($file); }

$username = 'admin';
$encrypted_passwordD = crypt($password, base64_encode($password));
$encrypted_passwordB = password_hash($password, PASSWORD_BCRYPT);
print "
$username . ':' . $encrypted_passwordB
";
$fileOUT = fopen("$supermonPath/.htpasswd",'w');fwrite ($fileOUT,"$username . ':' . $encrypted_passwordB");fclose ($fileOUT);

print "Building globalfor $call
";
buildGlobal($node);

$search="connpgm=";edit_config("connpgm=/usr/local/sbin/supermon/smlogger 1");
$search="discpgm=";edit_config("discpgm=/usr/local/sbin/supermon/smlogger 0"); 

// /etc/asterisk/logger.conf
$file=$logger;$search= "messages =>"; $in="messages => notice,warning,error,verbose";
edit_config($in);

// cronDel($in) cronAdd($in)  $search
$in="10,25,40,55 * * * * /usr/local/sbin/trimlog.sh /var/log/asterisk/messages 1000"; $search="trimlog.sh /var/log/asterisk/messages";
cronAdd($in);
print "cron $in
";

$file="/srv/http/index.php";

$fileOUT = fopen($file, "w"); 



$out = "<?php
header('Location: /supermon/link.php?nodes=$node');
die();
?>";    // <?php

fwrite ($fileOUT, $out);
fclose ($fileOUT);


exec ("astres.sh",$output,$return_var);  // Run restart

print"Supermon setup
";


}
start_menu($in);start("start");
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

function sleep($in){
global $sleep ;
$displaySleep="false";
if ($sleep){$displaySleep="true";}
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
Watchdog timmer.  [$watchdog] On this many falures take action.
In case of Not Registered it will activate my reg fix. Which bypasses a port block on your modem router or ISP.


Net down. error logs only.
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
global $sleep,$IconBlock,$debug,$AutoNode,$datum,$forcast,$beta,$saveDate,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$high,$hot,$nodeName,$reportAll,$watchdog;

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
           $forcast = $u[18];
              $beta = $u[19];
          $watchdog = $u[20];
             $debug = $u[21];   
    }
}



else {
// set default
$status ="Building default settings";save_task_log ($status);
print "$datum $status
";

$reportAll = false;$nodeName = "server";$high = 60;$hot  = 50;
$station="KIER";$level = 3 ;
$zipcode="71432"; $IconBlock= true; $skywarn =""; $lat ="31.7669"; $lon="-92.3888";$sleep=true;
$sayWarn=true;$sayWatch=true;$sayAdvisory=true;$sayStatement =true;$node = $AutoNode;$forcast= false;$beta = false; $debug=false; $watchdog = 5;
save ("save");
}
}


function save($in){
global $debug,$datum,$forcast,$beta,$AutoNode,$path,$node,$station,$level,$zipcode,$IconBlock,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$watchdog;
$fileOUT = fopen("$path/setup.txt", "w");
$status ="Writing settings";save_task_log ($status);
print "$datum $status
";
fwrite ($fileOUT, "$path,$node,$station,$level,$zipcode,$IconBlock,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll,$datum,$forcast,$beta,$watchdog,$debug,,,,,");
fclose ($fileOUT);
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

$files = "clear.wav,flood_advisory.wav,weather_service.wav,hot.ul,warning.ul,under-voltage-detected.ul,arm-frequency-capped.ul,currently-throttled.ul,soft-temp-limit-active.ul,under-voltage-detected.ul,arm-frequency-capping.ul,throttling-has-occurred.ul,soft-temp-limit-occurred.ul,advisory.gsm";
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
// install other  - setup.php is running so download to temp file - setup.php,
$files = "supermon.txt,supermon_weather.php,load.php,forcast.php,temp.php,cap_warn.php,weather_pws.php,sound_db.php,check_reg.php,nodelist_process.php,check_gmrs.sh,sound_db.php,sound_wav_db.csv,sound_gsm_db.csv,sound_ulaw_db.csv";
$repo2 = "https://raw.githubusercontent.com/tmastersmart/gmrs_live/main";
$error = "";
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
 exec("sudo chmod +x $file",$output,$return_var); 
 }

// special download of setup because its running. Will install elsewhere 
$path2 = "$path/update";
if(!is_dir($path2)){ mkdir($path2, 0755);}
chdir($path2); 
exec("sudo wget $repo2/setup.php ",$output,$return_var);
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
$file ="/usr/local/etc/allstar_node_info.conf";
$fileIN= file($file);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$line = str_replace('"', "", $line);
$pos = strpos($line, 'ODE1=');
if ($pos){$u= explode("=",$line);
$node=$u[1];}
$pos2 = strpos($line, 'CALL='); 
if ($pos2){$u= explode("=",$line);
$call=$u[1];}
}


$file= "$path/mm-node.txt";// This will be the AutoNode varable
$fileOUT = fopen($file, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$node,$call, , , ");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

//save_task_log ("Imported node:$node Call:$call");
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
global $path,$error,$datum,$file,$logRotate;

$datum  = date('m-d-Y H:i:s');
if(!is_dir("$path/logs/")){ mkdir("$path/logs/", 0755);}
chdir("$path/logs");
$file="$path/logs/log.txt";
$file2="$path/logs/log2.txt"; //if (file_exists($mmtaskTEMP)) {unlink ($mmtaskTEMP);} // Cleanup

// log rotation system
if (is_readable($file)) {
   $size= filesize($file);
   if ($size > $logRotate){
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





// function search and replace
//
function edit_config($in){
global $search,$path,$file,$tmpFile,$ok;
$ok=false;
if (file_exists($file)){

$fileBu = "$file-.bak"; if (file_exists($fileBu)){ unlink($fileBu); }
copy($file,$fileBu);if(!file_exists($fileBu)){ print "Unable to make a BackUP.";}

$fileIN= file($file);
$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", $search); if ($pos == 1){$line=$in;$ok=true;}  // Replace the found line with a new one.
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
}





// global.inc'
function buildGlobal($in){
global $global,$path,$file,$tmpFile,$ok,$password,$node;

$file = $global;

if (file_exists($file)){
$fileBu = "$file-.bak"; if (file_exists($fileBu)){ unlink($fileBu); }
copy($file,$fileBu);if(!file_exists($fileBu)){ print "Unable to make a BackUP.";}

$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");  // 


$formated="
<?php
// Set the values to your parameters
// ONLY change text between quotes
//
// Your callsign
#CALL = '$call';
//
// Your name
#NAME = 'YOUR NAME';
//
// Your location
#LOCATION = 'YOUR LOCATION';
//
// Second line header title
#TITLE2 = 'RPi2-3 Node';
//
// Third line header title
#TITLE3 = 'Allstar/IRLP/Echolink System Manager';
//
// Background image - specify path if not /srv/http/supermon
// Leaving BACKGROUND null results in BACKGROUND_COLOR
#BACKGROUND = 'background.jpg';
//
// Background color if no image
#BACKGROUND_COLOR = 'blue';
//
// Height of background - matches image height
#BACKGROUND_HEIGHT = '124px';
?>
";//  <?php
$formated = str_replace("'", '"', $formated);
$formated = str_replace('#', '$', $formated);

fwrite ($fileOUT, $formated);

fclose ($fileOUT);

save_task_log ("$file saved");

}
}
 

function buildAllmon($in){
global $allmon,$path,$file,$tmpFile,$ok,$password,$node;

$file = $allmon;

if (file_exists($file)){
$fileBu = "$file-.bak"; if (file_exists($fileBu)){ unlink($fileBu); }
copy($file,$fileBu);if(!file_exists($fileBu)){ print "Unable to make a BackUP.";}

$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");  // 

$formated="host=127.0.0.1:5038
user=admin
passwd=$password
menu=yes
system=Nodes
hideNodeURL=no

[All Nodes]
sstem=Display Groups
nodes=$node
menu=yes

[lsNodes]
url='/cgi-bin/lsnodes_web?node=$node'
menu=yes

[GMRSLive]
url=/http://gmrslive.com/status/link.php?nodes=700,900'
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
save_task_log ("$file saved");

}
}

function getRandomString($n){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!#*()-+=?<>';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    save_task_log ("IAX password $randomString");
    return $randomString;
    
}


// cronDel($in) cronAdd($in)  $search

function cronAdd ($in){

$file    = "$path/cron.txt";
$tmpFile = "$path/cron-new.txt"; if (file_exists($tmpFile)){ unlink($tmpFile); }
exec ("crontab -l > $file",$output,$return_var);
$ok=false;
$fileIN= file($file);
$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", $search); if ($pos == 1){$ok=true;}  
fwrite ($fileOUT, "$line\n");
}
if (!$ok){fwrite ($fileOUT, "$in\n");}
fclose ($fileOUT);

if (!$ok){
exec ("crontab $tmpFile",$output,$return_var);
$status ="Add to cron $in";save_task_log ($status);
}
else{save_task_log ("Add to chron (already in it) $in");}
}


function cronDel($in){
$file    = "$path/cron.txt";
$tmpFile = "$path/cron-new.txt"; if (file_exists($tmpFile)){ unlink($tmpFile); }
exec ("crontab -l > $file",$output,$return_var);

$fileIN= file($file);
$fileOUT = fopen($tmpFile, "w") or die ("Error $tmpFile Write falure\n");
foreach($fileIN as $line){
$ok=false;
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", $search); 
if ($pos == 1){$ok=true;}  
else{fwrite ($fileOUT, "$line\n");}
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
$search="weather_pws.php";$in="";cronDel($in);
$search="weather_pws.php";$in="";cronDel($in);
$search="cap_warn.php"   ;$in="";cronDel($in);
$search="temp.php"       ;$in="";cronDel($in);


// add back the orginal
$search="/usr/local/sbin/saytime";$in="00 8-23 * * * (source /usr/local/etc/allstar.env ; /usr/bin/nice -19 /usr/bin/perl /usr/local/sbin/saytime.p$";cronAdd($in);
}
// sets up cron and removes old scripts.
function setUpcron($in){
// comment out existing time string. 
//#00 8-23 * * * (source /usr/local/etc/allstar.env ; /usr/bin/nice -19 /usr/bin/perl /usr/local/sbin/saytime.p$
$search="/usr/local/sbin/saytime";$in="#00 8-23 * * * (source /usr/local/etc/allstar.env ; /usr/bin/nice -19 /usr/bin/perl /usr/local/sbin/saytime.p$";
cronAdd($in);

// add the new time string
$search="weather_pws.php";$in="00 * * * * php /etc/asterisk/local/mm-software/weather_pws.php >/dev/null";cronAdd($in);

// Remove autosky
//#*/23 * * * * /usr/local/bin/AUTOSKY/AutoSky
$search="AutoSky";$in="#*/23 * * * * /usr/local/bin/AUTOSKY/AutoSky";cronAdd($in);

// add skywarn
$search="cap_warn.php";$in="00 * * * * php /etc/asterisk/local/mm-software/cap_warn.php >/dev/null";cronAdd($in);


$search="temp.php";$in="*/27 * * * * php /etc/asterisk/local/mm-software/temp.php >/dev/null";
cronAdd($in) ;


//stop the looping script from being in memory
// /usr/local/bin/AUTOSKY/AutoSky
// /usr/local/bin/AUTOSKY/AutoSky.ON
$file="/etc/rc.local";  
$search="AutoSky";$in="";
edit_config($in); 
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
