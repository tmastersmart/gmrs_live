<?php
// (c)2023 by WRXB288 and LAgmrs.com  
// shared sound database lookup



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
if ($in==$u[2]){$filePlay="$u[0]/$u[1].gsm";break;}


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


?>
