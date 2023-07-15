<?php
// (c)2015/2023 by The Master lagmrs.com

//  "Auth. Sent" at Server
//  "Registered" at Server
//
//  Module is not to be loaded direct
//
// check if the node is reg



// Bridged detection.
// checks if this node is bridged
function bridge_check($in){
global $bridged,$node,$node1,$nodes,$path,$datum,$debug;

$file   = "/tmp/bridge_check.txt"; if(file_exists($file)){unlink($file);}
$status= exec("/bin/asterisk -rx 'rpt xnode $node' > $file",$output,$return_var);
$fileIN= file($file);$bridged=false;
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", "RPT_ALINKS"); // RPT_ALINKS=1,1195TU
if ($pos){ 
$u = explode("=",$line);// get the value
$u2 = explode(",",$u[1]);// break up the fields
$nodes=$u2[0]; 
if ($nodes >1){$bridged=true;}
  }
 }
$file = "/tmp/bridged_flag.txt"; if(file_exists($file)){unlink($file);}
if($debug){print"$datum DEBUG $u[0]=$u[1]\n";}
if($bridged) {
$out="node $node is Bridged $u[1]"; save_task_log ($out);print "$datum $out\n";
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,$out);flock ($fileOUT, LOCK_UN );fclose ($fileOUT); 
//  /etc/asterisk/local/mm-software/sounds/,bridged,bridged
}
 
 
}

function reg_check ($in){
global $counter,$datum,$node,$node1,$registered,$ip,$file,$path,$NotReg,$debug,$watchdog;

$file   = "/tmp/registered_check.txt"; if(file_exists($file)){unlink($file);}
//semaphore
$fileYes= "/tmp/registered_flag.txt";
$fileNo = "/tmp/not_registered_flag.txt";
$status= exec("/bin/asterisk -rx 'iax2 show registry' > $file",$output,$return_var);
$fileIN= file($file);
$node1="";$node2="";
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$line = str_replace(" ", ":", $line);
$u = explode(":",$line);  
if (!$node1){$host=$u[0];$port=$u[1];$dnsmge=$u[4];$node1=$u[11]; $ip=$u[30];$port2=$u[31]; $refRate=$u[39]; $registered=$u[41];}
//if (!$node2){$host2=$u[0];$port2=$u[1];$dnsmge2=$u[4];$node2=$u[11]; $ip2=$u[30];$registered2=$u[41];}
//print "$line
//";
}
if ($node <> $node1){$out="$node <> $node1";save_task_log ($out);print"$datum $out\n"; }
if ($ip =="<Unregistered>"){$registered="Unregistered";}

$datum   = date('m-d-Y H:i:s');
if($debug){$out="$node1 $ip $u[41]";save_task_log ($out);print"$datum DEBUG $out\n";}
print "$datum Node:$node1 is $registered port:$port";
if ($registered=="Registered"){
print "<OK!>
"; 
$file= $fileNo; if(file_exists($file)){unlink($file);}
$file= $fileYes;
}
else {
print "Error!"; 
save_task_log ("node:$node Detect node:$node1 $ip $registered");
$file= $fileYes; if(file_exists($file)){unlink($file);}
$file= $fileNo;
}
$fileOUT = fopen($file,'w');flock ($fileOUT, LOCK_EX );fwrite ($fileOUT,"$node1 $ip $port2 $registered");flock ($fileOUT, LOCK_UN );fclose ($fileOUT);  

}

// Take action to fix reg
function reg_fix ($in){
global $counter,$datum,$node1,$registered,$ip,$node,$file,$path,$NotReg,$watchdog,$debug,$newPort;
// Watchdog -----> restart AST asterisk (MMregister fix)
$newPort= rand(4500,4600); rotate_port("rotate");
sleep(10);
$datum   = date('m-d-Y H:i:s');
$eastBoundAndDown="'We gonna do what they say can't be done'"; 
$out="Trying to fix it.. $eastBoundAndDown";
save_task_log ($out);print "$datum $out
"; 
sleep(60);// give time for playing to finish 
$status= shell_exec("sudo /usr/local/sbin/astres.sh");
$datum   = date('m-d-Y H:i:s');
print"$datum Requesting a AST restart
";
sleep (60); // Give time to register
reg_check ("recheck");
}


//
// we check which port is in use
//
function find_port ($in){
global $port,$debug,$datum;
$port="";
$iax     =  "/etc/asterisk/iax.conf";
$fileIN= file($iax);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", "bindport="); 
if ($pos == 1){
if($debug){print"$datum DEBUG $line\n";}
$u= explode("=",$line);
$port = $u[1]; $port= trim($port," ");
return $port;
  }
 }
} 

// rotate the port to this number
// use extra level error checking
function rotate_port($in){  //$random = rand(4500,4600);   // rand(min,max); 
global $path,$newPort,$debug;

if(!$newPort){$newPort= rand(4500,4600);}
$datum = date('m-d-Y-H:i:s');
$cur   = date('mdyhis');
srand(time());

$savever = true ;
$iax     =  "/etc/asterisk/iax.conf";
$iaxbk   =  "/tmp/iax-$cur.conf";
$iaxtmp  =  "/etc/asterisk/iax-tmp.conf";
$status = "";
chdir("/etc/asterisk");
copy($iax,$iaxbk);
if (file_exists($iaxbk )){ // work from the backup
$fileOUT = fopen($iaxtmp, "w");
$fileIN= file($iaxbk);
foreach($fileIN as $line){
$line = str_replace("\r", "", $line);
$line = str_replace("\n", "", $line);
$pos = strpos("-$line", "bindport="); 
if ($pos == 1){$status = "$status $line changed to bindport=$newPort";$line="bindport=$newPort";

}
$pos = strpos("-$line", ";rotate_port"); if ($pos == 1){$line=";rotate_port $datum port:$newPort";$savever=false;}
fwrite ($fileOUT, "$line\n");
}
if($savever){ fwrite ($fileOUT, ";rotate_port $datum port:$newPort\n"); } 
fclose ($fileOUT);
 
if (file_exists($iaxbk)){ 
 unlink($iax); 
 if (!file_exists($iax)){ rename ($iaxtmp, $iax); }
 else{ $status="$status Unable to unlink file $iax for replacement";}
}
}
else {$status="$status Unable to make working backup $iaxbk";}

$datum = date('m-d-Y-H:i:s');
print "$datum $status 
";
save_task_log ($status);


}





?>
