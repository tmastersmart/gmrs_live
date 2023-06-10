<?php
// (c)2023 by WRXB288 and LAgmrs.com  
//
//   _____ _       __          __                 _____          _____  
//  / ____| |      \ \        / /                / ____|   /\   |  __ \ 
// | (___ | | ___   \ \  /\  / /_ _ _ __ _ __   | |       /  \  | |__) |
//  \___ \| |/ / | | \ \/  \/ / _` | '__| '_ \  | |      / /\ \ |  ___/ 
//  ____) |   <| |_| |\  /\  / (_| | |  | | | | | |____ / ____ \| |     
// |_____/|_|\_\\__, | \/  \/ \__,_|_|  |_| |_|  \_____/_/    \_\_|     
//               __/ |                                                  
//              |___/   
//
//https://api.weather.gov  module not to be called direct 
//
// This is the new api for the NWS. Still a work in process for now
//


//================================test=================
//https://api.weather.gov/zones/county/LAC043
//https://api.weather.gov/points/38.8894,-77.0352
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);
$poll_time = round($poll_time,2);
$forcastxml="/tmp/forcast.xml";
$forcastxml2="/tmp/forcast2.xml";
if (!isset($lat)){print"Module can not be run direct
";die;}
$domain ="api.weather.gov"; $url = "/points/$lat,$lon"; 
$datum  = date('m-d-Y H:i:s');
print "$datum Polling $domain >";
$file=$forcastxml;
$options = array(
    'http'=>array(
        'timeout' => 10,  //if it takes this long somethings wron
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
                  "Cookie: foo=bar\r\n" .
                  "User-Agent: Allstar Node lagmrs.com \r\n" // wont work unless we fake this )
));
$context = stream_context_create($options);
$html = @file_get_contents("https://$domain/$url",false,$context); 
if(!$html){print"error $poll_time Sec.
";}
else{
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$html);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
print "...";
read_api ($file);
print "...";
$file=$forcastxml2;
$options = array(
    'http'=>array(
        'timeout' => 10,  //if it takes this long somethings wron
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
                  "Cookie: foo=bar\r\n" .
                  "User-Agent: Allstar Node lagmrs.com \r\n" // wont work unless we fake this )
));
$context = stream_context_create($options);
$html = @file_get_contents("$url",false,$context);
if(!$html){print"error";}

else {
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$html);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
read_api ($file) ;
print "<ok>";
}
print "$poll_time Sec. 
";
$datum   = date('m-d-Y H:i:s');
print "$datum $shortForcast
";
//build a sound file
}




//================================test=================


 function read_api ($file){
global $shortForcast,$detailedForecast,$url,$warn1,$warn2,$warn3,$warn4,$data_good,$title,$clear,$file,$out,$summary1,$summary2,$summary3,$summary4;
// New API test
$shortForcast="";$url="";
$html= file($file);
$html = str_replace('"', "", $html);
foreach($html as $line){

$pos = strpos($line, 'forecast:');  //forecast": "https://api.weather.gov/gridpoints/SHV/128,37/forecast",
if ($pos) {
     $test = $line;
     $Lpos = strpos($test, 'http');$Rpos = strpos($test, 'cast,');
     $url  = substr($test, $Lpos,($Rpos-$Lpos)+4);
     }
$pos = strpos($line, 'shortForecast:');  
if ($pos) {
     $test = $line;
     $Lpos = strpos($test, ':');$Rpos = strpos($test, ',');
     $shortForcast  = substr($test, $Lpos+2,($Rpos-$Lpos)-2);
     }
$pos = strpos($line, 'detailedForecast:');  
if ($pos) {
     $test = $line;
     $Lpos = strpos($test, ':');$Rpos = strpos($test, ',');
     $detailedForecast  = substr($test, $Lpos+2,($Rpos-$Lpos)-2);
     }    
$pos = strpos($line, 'number: 2,');if ($pos) { return;}// stop here dont read past today     
}
}
