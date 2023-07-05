<?php
// (c)2023 by WRXB288 and LAgmrs.com  
// Import this into link.php on supermon
// added conditions by WRXB288
$copyrightcall="WRXB288";
$conditions="/tmp/conditions.txt";
$skywarn   ="/tmp/skywarn.txt";
$forcast   ="/tmp/forcast.txt";
$icons     ="/tmp/forcast_icons.txt"; 
$forcastWeekFile="/tmp/forcast_week.txt";
$alertTxtHeadline ="/tmp/skywarn_headline.txt";

print "
<!-- Weather by $copyrightcall ----->";// Add comments to html output (Human readable)
if (file_exists($conditions)) {
  $d = file_get_contents ($conditions); $size= filesize($conditions);   if ($size >=5){
    print "<p style='margin-top:0px;'> Local Weather conditions: [";
    print "<span style='margin-top:0px; background-color: GAINSBORO;'>&nbsp;<small>$d</small>&nbsp;</span>]";
}              }

print "
<!-- Weather by $copyrightcall ----->";
// This will be a compacked event , a stacked headline.
if (file_exists($skywarn)) {
  $d = file_get_contents ($skywarn);$size= filesize($skywarn);   if ($size >=5){
  print "<span style=\"color: red;\"><br><b>Alert(s): [$d]</b></span>";
} 

if (file_exists($alertTxtHeadline)) {
$d = file_get_contents ($alertTxtHeadline);
print"<br>";
$u= explode(",",$d);
foreach($u as $line){
print "<small>$line</small><br>";
}
}
}
print "
<!-- Weather by $copyrightcall ----->";
//  $shortForcast,$detailedForecast,$icon

if (file_exists($forcast)) {
  $d = file_get_contents ($forcast); $size= filesize($forcast);  if ($size >=9){
  $u = explode("|",$d);
  print "<br><img src='$u[2]' width=20 height=20><small>Forcast:[$u[1]]</small>
  ";
} 
}
print "
<!-- Weather by $copyrightcall ----->";
// The forcast icon block

if (file_exists($forcastWeekFile)) {
 $f = file_get_contents ($forcastWeekFile); $size= filesize($forcastWeekFile);
 $fc= explode("|",$f);
 }

print "
<!-- Weather by $copyrightcall ----->";
if (file_exists($icons)) {$d = file_get_contents ($icons); $size= filesize($icons);  

if ($size >=9){
print "<table border=0 cellpadding=5 cellspacing=5 style='border-collapse: collapse' id=forcast><tr>
";
  $u= explode("|",$d); $i=0;//$i=count($d);
  foreach($u as $line){
  $dateF = strtotime("+$i day");
  $day = date('D j', $dateF);
  if ($i==0){$day="Today";}
  if ($i==1){$day="Tomorow";}
  print"<td><div title='$fc[$i]'><img src='$line' width=40 height=40 alt='$i'/></div><small>$day</small></td>
  "; // will be over 7
  $i++;
}
print "</tr></table>"; 
}
}
print "
<!-- Weather by $copyrightcall ----->";
?>
