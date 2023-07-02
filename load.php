<?php
// (c)2023 by WXRB288 lagmrs.com  by pws.winnfreenet.com
// data file loader module..


load("load");




function load($in){
global $forcast,$beta,$saveDate,$path,$node,$station,$level,$zipcode,$skywarn,$lat,$lon,$sayWarn,$sayWatch,$sayAdvisory,$sayStatement,$testNewApi,$high,$hot,$nodeName,$reportAll;


if (is_readable("$path/setup.txt")) {
   $fileIN= file("$path/setup.txt");
//$datum = date('m-d-Y-H:i:s');
//print "$datum Loading settings
//";
   foreach($fileIN as $line){
    $u = explode(",",$line);
//            $path =  $u[0];
             $node  =  $u[1];
          $station  =  $u[2];
             $level =  $u[3];
         $zipcode   =  $u[4];
           $skywarn =  $u[5];
               $lat =  $u[6];
               $lon =  $u[7];
           $sayWarn =  $u[8];
          $sayWatch =  $u[9];
       $sayAdvisory = $u[10];
      $sayStatement = $u[11];
        $testNewApi = $u[12];

              $high = $u[13]; 
               $hot = $u[14];
          $nodeName = $u[15];
         $reportAll = $u[16];
          $saveDate = $u[17];
           $forcast = $u[18];
              $beta = $u[19];
    }
}
else {print "
Missing settings.txt file, load setup.php program to create!

";die;}
}
?>
