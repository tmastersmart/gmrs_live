 <?php
// (c)2023 by WXRB288 lagmrs.com  by pws.winnfreenet.com
// data file loader module..


// v1.6 06/01/2023 This is the first release with a mostly automated setup and installer.
// v1.7 06/02/2023 Debugging after moving to seperate subdirectory. 
// v1.8 06/03/2023 
// v2.0 06/09/2023 new databases . Rewrite of sound file system.
// v2.3 06/13/2023 Major finished release with setup and installer 
// v2.4 06/21/2023 many add ons reg fix new api alerts decoding
// 
// stage 1
// v2.0 06/29/2023 new core released  with seperate module versions#s
//                 Automated Reg down detection and automated fix
//                 Many changes to alerts,Alerts now play with time,Reg down notification is in cap_warn and weather_pws               
//                 Many changes to setup program. Auto install of super mon is a work in progress and wont be released until fully tested.


// stage 2
//                 First stages of a GMRS directory are working see the nodelist being created each day.
//                 The future plan is to create my own supermon front end once stage 1 is perfected. 

// stage 3
//                Stage 3 is to get the cost of the PI down by getting the node software to run on a $35 clone board
//                Bringing the cost of a node down under $100. with bf888 radio. I beleive there is a need for a low cost node

  
$coreVersion = "v2.0";

load("load");

$logRotate = 30000;// size to rotate at


function load($in){
global $IconBlock,$debug,$AutoNode,$datum,$forcast,$beta,$saveDate,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$high,$hot,$nodeName,$reportAll,$watchdog;

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
//      $spare      = $u[12];
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



else {print "
==================================================================
Missing settings.txt file, load setup program to create one!
Or you tried to load the file direct. 
***Try one of these***

php setup.php
php weather_pws.php
php cap_warn.php
php temp.php


";die;}
}


function line_end($in){
global $script_start,$datum,$soundDbWav,$soundDbGsm;
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_end = $mtime;$script_time = ($script_end - $script_start);$script_time = round($script_time,2);
$datum  = date('m-d-Y H:i:s');
$memory = memory_get_usage() ;$memory =formatBytes($memory);
print "$datum $in [Line end] Used:$memory $script_time Sec
===================================================
";
unset ($soundDbWav);
unset ($soundDbGsm);
unset ($soundDbUlaw);
die;
}

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}





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

//  Watchdog system 
// 
function watchdog ($in){
global $file1,$datum,$soundDbGsm,$node,$datum,$netdown,$NotReg,$action,$status,$counter,$counterNet,$path,$watchdog;
$NotReg=false;$netdown=false;$status="";$counter=0;$counterNet=0;
if (!isset($watchdog)) {$watchdog =5;}// error checking
// watch the internet okreg oknet net reg
$file= "/tmp/watchdog.txt"; if (file_exists($file)){
$line = file_get_contents($file);
$u= explode(",",$line);
if ($u[0]){$counter  = $u[0];}
if ($u[1]){$counterNet=$u[1];} 
}
if ($in == "okreg"){$counter=0;} // if reg then all is ok
if ($in == "oknet"){$counterNet=0;} // net can be ok and reg bad

if ($in == "net")  {
$counterNet++; $netdown =true;
$status ="WatchDog net---> $counterNet $status";save_task_log ($status);print "$datum $status
";
}
if ($in == "reg")  {
$counter++; $NotReg=true;
$status ="WatchDog reg---> $counter $status";save_task_log ($status);print "$datum $status
";
}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,"$counter,$counterNet");flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
}



?>
