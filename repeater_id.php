<?php
//This is the repeater sound control file. Whats listed here will play after the time
// 
$randomR = mt_rand(1, 5);
//$randomR = 4;
if ($randomR ==1){check_sound("la-nationwide-blip1");  if($file1){$action = "$action $file1";}}
if ($randomR ==2){check_sound("gmrs-22");              if($file1){$action = "$action $file1";}}
if ($randomR ==3){check_sound("louisiana_nationwide2");if($file1){$action = "$action $file1";}}// louisiana nationwide
if ($randomR ==4){check_sound("lagmrs_net");           if($file1){$action = "$action $file1";}}
if ($randomR ==5){check_sound("custom_bv");            if($file1){$action = "$action $file1";}}
?>
