<?php
// (c)2023 by WRXB288 and LAgmrs.com 
// This is the new api for the NWS.  
//
//
//https://api.weather.gov  module not to be called direct 
//
//   v1.4
//================================test=================
//
//   Accept: application/cap+xml
//   https://api.weather.gov/
//
if (!isset($lat)){print"Module can not be run direct
";die;}

//$debug = true;  // loads from the settings file now

$shortForcast="";$url=""; $apiVer="?"; $headline="";$event="";
if($lat and $lon){
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_start = $mtime;
$forcastxml  ="/tmp/geocode.xml";
$forcastxml2 ="/tmp/forcast.xml";
$forcastTxt  ="/tmp/forcast.txt";
$forcastIcons="/tmp/forcast_icons.txt";
$forcastWeekFile="/tmp/forcast_week.txt";
$alertxml    ="/tmp/skywarn.xml";


$domain ="api.weather.gov"; $url = "/points/$lat,$lon"; 
$datum  = date('m-d-Y H:i:s');
print "$datum Polling $domain GeoCode>";
$file=$forcastxml;
$options = array(
    'http'=>array(
        'timeout' => 20,  //if it takes this long somethings wrong
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
                  "Accept: application/cap+xml" .
                  "Cookie: foo=bar\r\n" .
                  "User-Agent: lagmrs.com $ver weather software\r\n" // Tell the NWS who we are
));
$context = stream_context_create($options);
$html = @file_get_contents("https://$domain/$url",false,$context);
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);$poll_time = round($poll_time,2);
 
if(!$html){
$status="<error1>";if($poll_time>=10){$status="$status timeout";}
save_task_log ("weather API GeoCode error $status $poll_time Sec.");
print "$datum $status $poll_time Sec.
";}
else{
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$html);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);

read_api ($file);
print "<ok> $poll_time Sec.
";}
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_start = $mtime;
print "$datum Polling $domain Forcast>";
$file=$forcastxml2;

$context = stream_context_create($options);
// the URL is pulled from above file.
$html = @file_get_contents("$url",false,$context);
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);$poll_time = round($poll_time,2);
 
if(!$html){
$status="<error2>";if($poll_time>=10){$status="$status timeout";}
save_task_log ("weather API forcast error $status $poll_time Sec.");
print "$datum $status $poll_time Sec.
";}

else {
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$html);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
read_api ($file) ;
print "<ok> $poll_time Sec.
";
}
print "$datum Polling $domain Alerts >";
// ---------------------------------------------------------------------------------------------


$domain ="api.weather.gov"; $url = "/alerts/active?point=$lat,$lon"; 
$datum  = date('m-d-Y H:i:s');

$file=$alertxml;
$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_start = $mtime;
$context = stream_context_create($options);
$html = @file_get_contents("https://$domain/$url",false,$context);

$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$poll_end = $mtime;
$poll_time = ($poll_end - $poll_start);$poll_time = round($poll_time,2);
 
if(!$html){
$status="<error3>";if($poll_time>=10){$status="$status timeout";}
save_task_log ("weather API CAP Warn error $status $poll_time Sec.");
print "$datum $status  $poll_time Sec.
";}
else{
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$html);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);
//print "...";
read_api ($alertxml);
print "<ok> $poll_time Sec.
";



//----------------------------------------------------------------------------------------------



$datum   = date('m-d-Y H:i:s');

 
//
// CAP Warn is processed by cap_warn.php only
// Weather_pws.php displays but does not process.


//  Filter the alearts. Remove unwanted and dupes
$clean="";$clean2="";
$d= explode(",",$headline);
$u= explode(",",$event); $i=-1;
foreach($u as $line){
$i++;
//$pos = strpos("-$line", "Warning");  if (!$sayWarn and $pos){ continue;} 
$pos = strpos("-$line", "Watch");    if (!$sayWatch and $pos){ continue;}
$pos = strpos("-$line", "Advisory"); if (!$sayAdvisory and $pos){ continue;}
$pos = strpos("-$line", "Statement");if (!$sayStatement and $pos){continue;}

if ($clean){$pos = strpos("-$clean", $line);     if ($pos){continue;}}// Get rid of the dupes. 

if($clean){
$clean="$clean,$line";
$clean2="$clean2,$d[$i]";
}
else {$clean=$line;$clean2=$d[$i];}


}
if ($event){print "$datum Raw Event(s): $event
$datum Cleaned Event(s): $clean
";
$event=$clean;$headline=$clean2;
}
if(!$event){$event="clear";}







if ($debug){
if ($description){print "$datum Description: $description
";}


if($headline){
print "$datum Headline: $headline
";}
}
// Forcast
if ($shortForcast){
print "$datum Forcast: $shortForcast
";}

if ($debug){
if ($detailedForecast){
print "$datum Detailed: $detailedForecast
";
}
}



// link to the supermon

if($forcast){

if($shortForcast){$file=$forcastTxt;$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,"$shortForcast|$detailedForecast|$icon");flock ($fileOUT, LOCK_UN );fclose ($fileOUT);}
build_week ($forcastxml2);
if($iconWeek){$file=$forcastIcons;$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$iconWeek);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);}
if($forcastWeek){$file=$forcastWeekFile;$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$forcastWeek);flock ($fileOUT, LOCK_UN );fclose ($fileOUT);}

}


}

}
else{$out="ERROR lat/lon not set"; save_task_log ($out);print "$datum $out
";}



//================================test=================


function read_api ($file){
global $head,$headline,$apiVer,$description,$event,$icon,$shortForcast,$detailedForecast,$url,$data_good,$title,$clear,$file,$out;
// New API   
// headline description 
$html= file($file); $apiVer="?";
foreach($html as $line){


$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$len= strlen($line);

//  "icon": "https://api.weather.gov/icons/land/night/tsra_hi,30/sct?size=medium",   might contain a comma
$pos = strpos($line, 'icon":');  // "icon": "https://api.weather.gov/icons/land/day/tsra_hi,20?size=medium",
if ($pos) {
     $test = trim($line," "); 
     $Lpos = strpos($test, ':');;$Rpos = strpos($test, '",');
     $icon  = substr($test, $Lpos+3, ($Rpos-$Lpos)-3);
     $icon  = str_replace('size=medium', 'size=small', $icon);// Redirect to the smaller icons  small medium and large avalable
//   print "$icon";
}


$pos = strpos($line, '"headline":'); // "headline": "Heat Advisory issued June 25 at 4:22AM CDT until June 25 at 7:00PM CDT by NWS Shreveport LA",
if ($pos) {
     $test = $line; 
     $Lpos = strpos($test, ':');$Rpos = strpos($test, '",');
     $headline2 = substr($test, $Lpos+3,($Rpos-$Lpos)-3);
     $headline2 = str_replace(',', "", $headline2);// just in case TWS breaks things with ,
     $headline2 = trim($headline2," ");
     if($headline2 and $headline){
     $headline ="$headline,$headline2";
     $headline2="";
     }
     else {$headline=$headline2;}
     }    


          
$line = str_replace('"', "", $line);     
     
  
     
     
$pos = strpos($line, 'event:'); //  "event": "Heat Advisory",
if ($pos) {
     $test = $line;
     $Lpos = strpos($test, ':');$Rpos = strpos($test, ',');
     $event2 = substr($test, $Lpos+2,($Rpos-$Lpos)-2);
     if($event2 and $event){
     $event ="$event,$event2";// stacks events
     $event2="";
     }
     else {$event=$event2;}
     }
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
     $shortForcast = str_replace(',', "", $shortForcast);// just in case TWS breaks things with ,
     $shortForcast = trim($shortForcast," ");
     }
     

$pos = strpos($line, 'version:'); //"@version": "1.1",   API still shows 1.1 but may change to 1.2
if ($pos) {
     $test = $line;
     $Lpos = strpos($test, ':');$Rpos = strpos($test, ',');
     $apiVer  = substr($test, $Lpos+2, $len-2);
     $apiVer = str_replace(',', "", $apiVer); // just in case TWS breaks things with ,
     }     
$pos = strpos($line, 'detailedForecast:'); // "detailedForecast": "Mostly sunny, with a high near 94. Northeast wind 0 to 10 mph."
if ($pos) {
     $test = $line;
     $Lpos = strpos($test, ':');$Rpos = strpos($test, ',');
     $detailedForecast = substr($test,$Lpos+2, $len-2);
     $detailedForecast = str_replace(',', "", $detailedForecast);// just in case TWS breaks things with ,
     $detailedForecast = trim($detailedForecast," ");
     }    

$pos = strpos($line, 'description:');  // "description": "* WHAT...Heat index 
if ($pos) {
     $test = $line;
     $Lpos = strpos($test, ':');$Rpos = strpos($test, ',');
     $description2 = substr($test, $Lpos+2,($Rpos-$Lpos)-2);
     $description2 = str_replace(',', "", $description2);// just in case TWS breaks things with ,
     $description2 = trim($description2," ");
     // stack into an array
     if($description2 and $description){
      $description ="$description,$description2";
      $description2="";
     }
     else {$description=$description2;}
     }   





$pos = strpos($line, 'number: 2,');if ($pos) { return;} 
// stop here dont read past today 
 
  }
}

function build_week ($file){
global $iconWeek,$forcastWeek;
$forcastWeek="";
$testI= file($file); 
foreach($testI as $line){

$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);



//  "icon": "https://api.weather.gov/icons/land/night/tsra_hi,30/sct?size=medium",   might contain a comma
$pos = strpos($line, 'icon":');  // "icon": "https://api.weather.gov/icons/land/day/tsra_hi,20?size=medium",
if ($pos) {
     $test = trim($line," "); $len= strlen($test);
     $Lpos = strpos($test, ':');;$Rpos = strpos($test, '",');
     $icon2  = substr($test, $Lpos+3, ($Rpos-$Lpos)-3);
     $icon2  = str_replace('size=medium', 'size=small', $icon2);// Redirect to the smaller icons  small medium and large avalable
//     print "$icon2|";
     if($icon2 and $iconWeek){
     $iconWeek ="$iconWeek|$icon2";// stacks a week of icons into array use | not ,
     $icon2="";
     }
     else {$iconWeek=$icon2;}
     }
$line = str_replace('"', "", $line);        
     
//detailedForecast
$pos = strpos($line, 'detailedForecast:');   
if ($pos) {
     $test = $line;
     $Lpos = strpos($test, ':');$Rpos = strpos($test, ',}');
     $forcast  = substr($test, $Lpos+2,($Rpos-$Lpos)-1);
     $forcast = str_replace(',', "", $forcast);// just in case TWS breaks things with ,
     $forcast = trim($forcast," ");
      if($forcastWeek and $forcast){
     $forcastWeek ="$forcastWeek|$forcast";// stacks a week into array use | not ,
     $forcast="";
     }
     else {$forcastWeek=$forcast;}
     }
  }
}

