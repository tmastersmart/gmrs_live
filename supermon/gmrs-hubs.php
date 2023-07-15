<?php
// GMRSlive  Mods for Supermon (c)2023 
// Dont use on anythin else

                                           
include("session.inc");
include("header.inc");
$path = dirname(__FILE__) ;
$version = phpversion();
$datum = date('m-d-Y-H:i:s');

print "List of GMRS HUBS running on GMRS Live<br><br>";

print "<table border=2 cellpadding=0 cellspacing=0 style=\"border-collapse: collapse\" bordercolor=\"#111111\"  id=\"AutoNumber1\">
<tr><td>NODE</td><td>Name</td><td>city</td><td>call</td><td>type</td></tr>";

$filename =  "/etc/asterisk/local/mm-software/nodelist/hubs.csv";

$fileIN= file($filename);
foreach($fileIN as $line){
$u = explode("|",$line);
$count++;
$i++;
if ($i == 1){print"<tr bgcolor=pink>";}
if ($i == 2){print"<tr bgcolor=white >";$i=0;}

print "<td>$u[0]</td><td>$u[1]</td><td>$u[2]</td><td>$u[3]</td><td>$u[4]</td></tr>\n";
}
 
 

print"</table>";

print "<p>Total nodes $count</p>";

include ("footer.inc");
?>
