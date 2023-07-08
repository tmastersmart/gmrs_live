<?php
// (c)2015/2023 by The Master lagmrs.com
//
// This builds a new cleaned nodelist
// for future supermon use
//
// http://register.gmrslive.com/cgi-bin/privatenodes.txt
// We dont pull this we let the node do it 
// /var/log/asterisk/astdb.txt <-using this one


// v1.2 Beta
function sort_nodes ($in){
global $beta,$path,$node,$datum;

if (!$beta){return;} // Beta function still in testing.



$pathNode="$path/nodelist";
if(!is_dir($pathNode)){ mkdir($pathNode, 0755);}
$nodelist  =  "/var/log/asterisk/astdb.txt";
$nodelist2 = "$pathNode/dirty.csv";
$newfile  =  "$pathNode/clean.csv";
$newfile2 =  "$pathNode/repeaters.csv";
$newfile3 =  "$pathNode/hubs.csv";

if (file_exists($nodelist2)){
 $ft = time()-filemtime($nodelist2);
 if ($ft < 48 * 3600){ return; }
}
$datum = date('[H:i:s]');$out="Updating Nodelist";save_task_log ($out); 
print "$datum $out";
copy($nodelist,$nodelist2);

$fileOUT3 = fopen($newfile3, "w") ;
$fileOUT2 = fopen($newfile2, "w") ;
$fileOUT  = fopen($newfile,  "w") ;

$fileIN= file($nodelist2);
natsort($fileIN);
foreach($fileIN as $line){
 //Remove line feeds
  $line = str_replace("\r", "", $line);
  $line = str_replace("\n", "", $line);
  
$u = explode("|",$line);

// Extra error checking
if(!isset($u[0])){$u[0]="";}
if(!isset($u[1])){$u[1]="";}
if(!isset($u[2])){$u[2]="";}
if(!isset($u[3])){$u[3]="";}
if(!isset($u[4])){$u[4]="";}


$u[2] = str_replace("-", "", $u[2]);// remove from address

//  using() instead of []
$u[1] = str_replace("(", "[", $u[1]);$u[1] = str_replace(")", "]", $u[1]); 
$u[2] = str_replace("(", "[", $u[2]);$u[2] = str_replace(")", "]", $u[2]); 
$u[3] = str_replace("(", "[", $u[3]);$u[3] = str_replace(")", "]", $u[3]); 


if ($u[0]==1000){// erase the header
    if($u[1]=="WB6XYZ"){
    $u[0]= 0;
    }     
 }

//1985|Inactive
//1986|Inactive
//1987|Inactive
//1988|Inactive
//1989|Inactive
$test= "-$u[1] $u[2]";
// Unsure what this is remove it 
$pos = strpos($test, "Inactive");if ($pos){$u[0]=0;}
 
   
// 10 is blank 300 is active
if ($u[0]>1){  
// if no call then its a repeater or a HUB- Guess HUB
if (!$u[3]){$u[4]="H";} 

// Auto Create the type field

$pos = strpos($test, "Repeater") ;if ($pos){$u[4]="R";}
//$pos = strpos($test, "GMRS Live");if ($pos){$u[4]="H";}
$pos = strpos($test, "Hub");      if ($pos){$u[4]="H";}
$pos = strpos($test, "HUB");      if ($pos){$u[4]="H";}
$pos = strpos($test, "Node");     if ($pos){$u[4]="N";}
$pos = strpos($test, "NODE");     if ($pos){$u[4]="N";}
$pos = strpos($test, "Zello");    if ($pos){$u[4]="Z";}
$pos = strpos($test, "ZELLO");    if ($pos){$u[4]="Z";}
$pos = strpos($test, "MOBILE");   if ($pos){$u[4]="N";}
$pos = strpos($test, "DVswitch"); if ($pos){$u[4]="D";}
$pos = strpos($test, "Emergency");if ($pos){$u[4]="H";}

// Not repeaters but a hub
if ($u[0] == 1195){$u[4]="H";}
 
// These are known repeaters. that fail autodetect
if ($u[0] == 1050){$u[4]="R";}
if ($u[0] == 1051){$u[4]="R";}
if ($u[0] == 1052){$u[4]="R";}
if ($u[0] == 1053){$u[4]="R";}
if ($u[0] == 1054){$u[4]="R";}

//1235|Manchac 675 - The Road Kill !!|Prairieville,LA|
//1236|Broadmoor 600 The Roadkill !!|Baton Rouge,LA|
//1237|Crescent City Connection 600 The Road Kill !!|New Orleans,LA|
//1238|Broadmoor 600 - The Road Kill !!|Baton Rouge,LA|
//1239|Lacombe 625 The Road Kill !! Lacombe, LA||

if ($u[0] == 1235){$u[4]="R";}
if ($u[0] == 1236){$u[4]="R";}
if ($u[0] == 1237){$u[4]="R";}
if ($u[0] == 1238){$u[4]="R";}
if ($u[0] == 1239){$u[4]="R";}
if ($u[0] == 1513){$u[4]="R";}
if ($u[0] == 1531){$u[4]="R";}
if ($u[0] == 1532){$u[4]="R";}
if ($u[0] == 1533){$u[4]="R";}
if ($u[0] == 1534){$u[4]="R";}
if ($u[0] == 1730){$u[4]="R";}
if ($u[0] == 1731){$u[4]="R";}
if ($u[0] == 1733){$u[4]="R";}
if ($u[0] == 2341){$u[4]="R";}
if ($u[0] == 2342){$u[4]="R";}


// These dont look like hubs force NODE
if ($u[0] == 1120){$u[4]="N";}
if ($u[0] == 1121){$u[4]="N";}
if ($u[0] == 1122){$u[4]="N";}
if ($u[0] == 1123){$u[4]="N";}
if ($u[0] == 1124){$u[4]="N";}
if ($u[0] == 1150){$u[4]="N";}
if ($u[0] == 1151){$u[4]="N";}
if ($u[0] == 1152){$u[4]="N";}
if ($u[0] == 1153){$u[4]="N";}
if ($u[0] == 1154){$u[4]="N";}

// hubs 
if ($u[0] == 900){$u[4]="H";}
if ($u[0] == 921){$u[4]="H";}
if ($u[0] == 922){$u[4]="H";}
if ($u[0] == 923){$u[4]="H";}

if ($u[0] == 2148){$u[4]="H";} //2148|Remote to WRQL436 462.550|Beatty,OR|[WRTX950]




 // false detections
if ($u[0]==750) {$u[4]= "Z";}
if ($u[0]==7501){$u[4]= "Z";} 

   
if ($u[0]==1105){$u[4]= "N";}
if ($u[0]==2978){$u[4]= "N";}// No named node?????


// Texas GMRS data 2250 - 2267 are corrupted. 
// This will auto fix. To Be removed.....
// 1 is blank 2 contains the data that should  be in 1

//2250||Texas GMRS Network - Statewide Link|
//2251||North Texas Hub|
//2252||South Texas Hub|
//2253||East Texas Hub|
//2254||West Texas Hub|
//2255||Memorial Park 550 Repeater|
//2256||Northwest Houston 725 Repeater|
//2257||Channelview 675 Repeater|
//2258||Dallas County REACT 675 Repeater|
//2259||Lufkin 725 Repeater|
//2260||Dickinson 650 Repeater|
//2261||La Marque 700 Repeater|
//2262||Montgomery 600 Repeater|
//2263||Conroe 700 Repeater|
//2264||La Grange 725 Repeater|
//2265||Lubbock 700 Repeater|
//2266||Sugar Land 600 Repeater|
//2267||Chappell Hill 650 Repeater|
//2268||Amarillo 650 Repeater|  

//slide over data to fix corruped entries above
if ($u[1] ==""){ $u[1]=$u[2];if($u[3]!=""){$u[2]=$u[3];$u[3]="";}}


//1040|Thomas,- Hammond, IN|(WRCW750)
//1041|Thomas,- Hammond, IN|(WRCW750)
//1042|Thomas|- Hammond, IN|(WRCW750)
//1043|Thomas|- Hammond, IN|(WRCW750)
//1044|Thomas|- Hammond, IN|(WRCW750)

// 1040 and 1041 have the wrong field have a , in place of a | 
// TBR if fixed
if ($u[0] ==1040){$u[1]="Thomas";$u[2]="Hammond, IN";$u[3]="[WRCW750]";$u[4]="N";}
if ($u[0] ==1041){$u[1]="Thomas";$u[2]="Hammond, IN";$u[3]="[WRCW750]";$u[4]="N";}



// FIX IDs in the wrong fields  ( this may all be fixed now?)
$posL = strpos($u[1], "[W"); 
if ($posL>=1){
    $test = explode("[",$u[1]);$id= explode("]",$test[1]);
    $u[3]="[$id[0]]";
    }
// FIX IDs in the wrong fields 
$posL = strpos($u[2], "[W"); 
if ($posL>=1){
    $test = explode("[",$u[2]);$id= explode("]",$test[1]);
    $u[3]="[$id[0]]";
    }
// ID in wrong field 2 move to 3
$posL = strpos($u[2], "W");$posR = strpos($u[2], "]"); 
if($posL==1){
 if($posR==8){
  $u[3]= "$u[2]";
  $u[2]= "";
 }
}

// fix states
$state = explode(",",$u[2]);

// Extra error checking
if(!isset($state[0])){$state[0]="";}
if(!isset($state[1])){$state[1]="";}


$state[1] = strtoupper($state[1]);
$state[1] = str_replace(" ", "", $state[1]);

if ($state[1]=="ALABAMA") {$state[1]="AL";}
if ($state[1]=="COLORADO"){$state[1]="CO";}
if ($state[1]=="GEORGIA") {$state[1]="GA";}
if ($state[1]=="NEWYORK") {$state[1]="NY";}
if ($state[1]=="INDIANA") {$state[1]="IN";}
if ($state[1]=="TEXAS")   {$state[1]="TX";}
if ($state[1]=="GEORGIA") {$state[1]="GA";}
if ($state[1]=="TXUSA")   {$state[1]="TX";}
if ($state[1]=="NCUSA")   {$state[1]="NC";}
if ($state[1]=="FLORIDA") {$state[1]="FL";}
if ($state[1]=="NORTHCAROLINA") {$state[1]="NC";}
if ($state[1]=="PENNSYLVANIA")  {$state[1]="PE";}
if ($state[1]=="OHIO")  {  $state[1]="OH";}
if ($state[1]=="OKLAHOMA"){$state[1]="OK";}
if ($state[1]=="OREGON")  {$state[1]="OR";}
if ($state[1]=="IDAHO")  { $state[1]="ID";}


$test = str_replace(" ", "", $state[0]);

if ($test=="Michigan"){    $state[1]="MI";$state[0]="";}
if ($test=="SanAntonioTx"){$state[1]="TX";$state[0]="San Antonio";}
if ($test=="BuckeyeAz"){   $state[1]="AZ";$state[0]="Buckeye";}
if ($test=="AshevilleNC"){ $state[1]="NC";$state[0]="Asheville";}
if ($test=="PlanoTX"){     $state[1]="TX";$state[0]="Plano";}
if ($test=="ElkhartIn"){   $state[1]="IN";$state[0]="Elkhart";}
 
$state[0]= ltrim( $state[0]);// Remove the leading spaces from the address
 
if($state[1]){  $u[2]= "$state[0],$state[1]";  } // cleaned up 

$u[3] = strtoupper($u[3]); // convert all IDS to upercase   

$u[0]=trim($u[0]);
$u[1]=trim($u[1]);
$u[2]=trim($u[2]);
$u[3]=trim($u[3]); 



fwrite ($fileOUT, "$u[0]|$u[1]|$u[2]|$u[3]\n");
if($u[4]=="R"){fwrite ($fileOUT2, "$u[0]|$u[1]|$u[2]|$u[3]|$u[4]\n");}
if($u[4]=="H"){fwrite ($fileOUT3, "$u[0]|$u[1]|$u[2]|$u[3]|$u[4]\n");}
 }
} 
fclose ($fileOUT);fclose ($fileOUT2);
print "<ok>
";
}
