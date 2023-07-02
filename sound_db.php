<?php
// (c)2023 by WRXB288 and LAgmrs.com  
// shared sound database lookup


$soundDbWav = file("$path/sound_wav_db.csv");
$soundDbGsm = file("$path/sound_gsm_db.csv");
$soundDbUlaw= file("$path/sound_ulaw_db.csv");


$vpath ="/var/lib/asterisk/sounds";// old non database path


function check_wav_db ($in){
global $file1,$datum,$soundDbWav;
$filePlay=""; $file1="";
// search the database for the filename/location
foreach($soundDbWav as $line){
$u = explode(",", $line);
if ($in==$u[1]){$filePlay=$u[0];break;}
}
if (file_exists("$filePlay.wav")){$file1 = "$filePlay.wav";}
else{
print"$datum word [$in] not in database
";}
}

function check_gsm_db ($in){
global $file1,$datum,$soundDbGsm;
$filePlay="";
// kludge to force weather sounds to match what we have
if($in=="thunderstorms"){$in="thunderstorm";}
if($in=="chance"){$in="chance-of";}
if($in=="showers"){$in="rain";} 
if($in=="nws"){$in="national weather service";} 
if($in=="then"){$in="later";}
 
// search the database for the filename/location
foreach($soundDbGsm as $line){
$u = explode(",", $line);
if (isset($u[2])){if ($in==$u[2]){$filePlay="$u[0]/$u[1].gsm";break;}}


}
if (file_exists($filePlay)){$file1 = $filePlay;}
else{
print"$datum word [$in] not in database
";$file1="";
}
}

function check_ulaw_db ($in){
global $file1,$datum,$soundDbUlaw;
$filePlay="";
// search the database for the filename/location
foreach($soundDbUlaw as $line){
$u = explode(",", $line);
if ($in==$u[1]){$filePlay=$u[0];}
}
if (file_exists("$filePlay.ulaw")){$file1 = "$filePlay.ulaw";}
else{
print"$datum file [$in] not in database
";}
}

// v1 non database number module 
function make_number ($in){
global $vpath,$file0,$file1,$file2,$file3,$negative,$oh;
// Speak all possible numbers
// PHP Number matrix
$vpath ="/var/lib/asterisk/sounds";
$file0 = "";$file1 = "";$file2 = "";$file3 = "";$negative="";
if ($in <0 ){$negative = "$vpath/digits/minus.gsm";}
$in = abs($in);
$in = round($in);
if ($oh){if ($in<10) {    $file1  = "$vpath/digits/oh.gsm";}}
if ($in>=100 and $in<200 ){$file1  = "$vpath/digits/1.gsm";}
if ($in>=200 and $in<300 ){$file1  = "$vpath/digits/2.gsm";}
if ($in>=300 and $in<400 ){$file1  = "$vpath/digits/3.gsm";}
if ($in>=400 and $in<500 ){$file1  = "$vpath/digits/4.gsm";}
if ($in>=500 and $in<600 ){$file1  = "$vpath/digits/5.gsm";}
if ($in>=600 and $in<700 ){$file1  = "$vpath/digits/6.gsm";}
if ($in>=700 and $in<800 ){$file1  = "$vpath/digits/7.gsm";}
if ($in>=800 and $in<900 ){$file1  = "$vpath/digits/8.gsm";}
if ($in>=900 and $in<1000 ){$file1 = "$vpath/digits/9.gsm";}


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

if ($file3){$temp=$file2;$file2=$file3;$file3=$temp;}// Rearange for 100
         
}


function add_word($in){
global $datum,$path;


print"$datum word [$in] not in database
";

$file="$path/logs/words_to_add.txt";

// log rotation system
//if (is_readable($file)) {
//   $size= filesize($file);
//   if ($size > $logRotate){
//    if (file_exists($file2)) {unlink ($file2);}
//    rename ($file, $file2);
//    if (file_exists($file)) {print "error in log rotation";}
//   }
//}
//
//$fileOUT = fopen($file, 'a+') ;
//flock ($fileOUT, LOCK_EX );
//fwrite ($fileOUT, "$datum,$status,,\r\n");
//flock ($fileOUT, LOCK_UN );
//fclose ($fileOUT);


}

?>
