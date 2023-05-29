<?php
//  ------------------------------------------------------------
//  (c) 2023 by lagmrs.com all rights reserved
//  Permission granted to install and use with 
//  hubitat,gmrs,hamvoip.No modifications   
//  https://github.com/tmastersmart/
//
//   crontab -e and add this to bottom
//   02 12 * * * php /etc/asterisk/local/rotate_port.php
//
//
//  Rotates port so it will work with att fixed wireless or other isps
//  that block the register after a day or 2.
//  ------------------------------------------------------------
$node="2955";

define('TIMEZONE', 'America/Chicago');

print " =============================================
";
print " Bypass ISP port blocking
"; 
print "(c) 2023 iax port rotation script
";
print " =============================================
";


$datum = date('m-d-Y-H:i:s');
$cur   = date('mdyhis');
srand(time());
$random = rand(70,90);   // rand(min,max);
$savever = true ;
$version = "v1.0" ;

$iax     =  "/etc/asterisk/iax.conf";
$iaxbk   =  "/tmp/iax-$cur.conf";
$iaxtmp  =  "/etc/asterisk/iax-tmp.conf";
$log     =  "/tmp/port.log";


$status = "";
$action = "";
chdir("/etc/asterisk");

copy($iax,$iaxbk);if(!file_exists($iaxbk)){ $status="Unable to BackUP.";}

if (file_exists($iax )){
$fileIN= file($iax);
$fileOUT = fopen($iaxtmp, "w") or die ("Error $iaxtmp Write falure\n");
$fileIN= file($iax);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", "bindport="); 
if ($pos == 1){$status = "$status $line changed to  bindport=$random";$line="bindport=$random";}
$pos = strpos("-$line", ";rotate_port"); if ($pos == 1){$line=";rotate_port $datum $version port:$random";$savever=false;}
fwrite ($fileOUT, "$line\n");
}
 
if($savever){ fwrite ($fileOUT, ";rotate_port $datum $version port:$random\n"); } 
fclose ($fileOUT);
//fclose ($fileIN); 
 
if (file_exists($iaxbk)){ unlink($iax); if (!file_exists($iax)){ rename ($iaxtmp, $iax); }
 else{ $status="$status ERROR can not unlink file $iax";}
}
}
else {$status="Error Missing $iax";}


print "$datum $status \n";
$fileOUT = fopen($log, "a") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "$datum $status \n");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

//$fileOUT = fopen("/tmp/talk.txt", "w");flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, "port rotation script $status");flock( $fileOUT, LOCK_UN );fclose ($fileOUT);

$currentTime = "/tmp/port.gsm";
$vpath="/var/lib/asterisk/sounds";
$file=$currentTime; $cmd="";
if(file_exists($file)){unlink($file);}
$fileOUT = fopen($file,'wb');flock ($fileOUT, LOCK_EX );
check_name ("portnumber"); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
check_name ("changing"); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
check_name ("to"); if ($file1){ $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
$oh=false;make_number ($random);
if (file_exists($file1)){  $fileIN = file_get_contents ($file1);file_put_contents ($file,$fileIN, FILE_APPEND);}
if (file_exists($file2)){  $fileIN = file_get_contents ($file2);file_put_contents ($file,$fileIN, FILE_APPEND);}
flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
$datum   = date('m-d-Y H:i:s');
print "$datum Playing file to NODE:$node $currentTime
";
$status= exec("sudo asterisk -rx 'rpt localplay $node /tmp/port '",$output,$return_var);
if(!$status){$status="OK";}
print "$datum finished  $status $return_var
";
print " =============================================
";        




function make_number ($in){
global $vpath,$file0,$file1,$file2,$file3,$negative,$oh;
// Speak all possible numbers
// PHP Number matrix

$file0 = "";$file1 = "";$file2 = "";$file3 = "";$negative="";
if ($in <0 ){$negative = "$vpath/digits/minus.gsm";}
$in = abs($in);
$in = round($in);
if ($oh){if ($in<10) {    $file1  = "$vpath/digits/oh.gsm";}}
if ($in >= 100){          $file3  = "$vpath/digits/hundred.gsm"; $in = ($in -100); }
if ($in>=20 and $in<30  ){$file1  = "$vpath/digits/20.gsm";$in=$in-20;} 
if ($in>=30 and $in<40  ){$file1  = "$vpath/digits/30.gsm";$in=$in-30;}
if ($in>=40 and $in<50  ){$file1  = "$vpath/digits/40.gsm";$in=$in-40;} 
if ($in>=50 and $in<60  ){$file1  = "$vpath/digits/50.gsm";$in=$in-50;}
if ($in>=60 and $in<70  ){$file1  = "$vpath/digits/60.gsm";$in=$in-60;} 
if ($in>=70 and $in<80  ){$file1  = "$vpath/digits/70.gsm";$in=$in-70;}
if ($in>=80 and $in<90  ){$file1  = "$vpath/digits/80.gsm";$in=$in-80;} 
if ($in>=90 and $in<100 ){$file1  = "$vpath/digits/90.gsm";$in=$in-90;}

if ($in >=1 and $in<20  ){$file2  = "$vpath/digits/$in.gsm";}           
}

function check_name ($in){
global $vpath,$file1;
$file1="";
$fileSound= "$vpath/$in.gsm";if (file_exists($fileSound)){$file1 = $fileSound;}
}

?>
  
