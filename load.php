 <?php
// (c)2023 by WXRB288 lagmrs.com  by pws.winnfreenet.com
// data file loader module..


  
$coreVersion = "v2.3";
load("load");
$logRotate = 40000;// size to rotate at
$piVersion = file_get_contents ("/proc/device-tree/model");


function load($in){
global $tts,$sleep,$IconBlock,$debug,$AutoNode,$datum,$forcast,$beta,$saveDate,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$high,$hot,$nodeName,$reportAll,$watchdog,$bridgeCheck;

if (is_readable("$path/setup.txt")) {
   $fileIN= file("$path/setup.txt");
//$datum = date('m-d-Y-H:i:s');
//print "$datum Loading settings
//";
   foreach($fileIN as $line){
    $u = explode(",",$line);
//            $path =  $u[0];// basicaly a header
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
             $sleep = $u[12];
              $high = $u[13]; 
               $hot = $u[14];
          $nodeName = $u[15];
         $reportAll = $u[16];
          $saveDate = $u[17];
//         $forcast = $u[18]; // not using
              $beta = $u[19];
          $watchdog = $u[20];
             $debug = $u[21];
//             $tts = $u[22]; // not yet used     
       $bridgeCheck = $u[23];          
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
global $path,$script_start,$datum,$soundDbWav,$soundDbGsm;
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$script_end = $mtime;$script_time = ($script_end - $script_start);$script_time = round($script_time,2);
$datum  = date('m-d-Y H:i:s');
$memory = memory_get_usage() ;$memory =formatBytes($memory);
print "$datum $in [Line end] Used:$memory $script_time Sec
";
unset ($soundDbWav);
unset ($soundDbGsm);
unset ($soundDbUlaw);


// finish any upgrade in progress
$path2 = "$path/update";
$file  = "$path2/setup.php";
$file2 = "$path/setup.php";
if (file_exists($file)) {
print "$datum Cleaning up after upgrade
";
copy($file2, "$path2/setup_old_php.bak");
copy($file, $file2); 
if (file_exists($file2)){unlink($file);}
}

print"===================================================
";

die;
}

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}


// v2 stop using $file confusion  
function save_task_log ($status){
global $path,$error,$datum,$logRotate;

// $logRotate = 40000;// size to rotate at set at top

$datum  = date('m-d-Y H:i:s');
if(!is_dir("$path/logs/")){ mkdir("$path/logs/", 0755);}
chdir("$path/logs");
$log="$path/logs/log.txt";
$log2="$path/logs/log2.txt"; 


// log rotation system 
// To be replaced with daily logs....   
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
