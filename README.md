# gmrslive.com scripts


A collection of scripts that will work on gmrslive nodes

download the installer.

ch /etc/asterisk/local/

sudo wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install.php

run the custom installer

php install.php

This will install the following

temp.php and its sound files
edit the temp file and add your node number
nano temp.php       
run it
php temp.php 
you can now add it to cron
//crontab -e add the following for time on the hr between 6am and 11pm
// at 30 mins on the hr to prevent overrun with time.
// 30 7-23 * * * php /etc/asterisk/local/temp.php >> /dev/null

weather_pws.php
This is a custom time and temp system that reads data from 
mesowest, madis, APRSWXNET/Citizen Weather Observer Program (CWOP)
It allows you to pick your station. See the madis map for station numbers
http://www.wxqa.com/  (CWOP) main website
https://mesowest.utah.edu/
https://madis-data.ncep.noaa.gov
https://aprs.fi/

Once you have a station id edit the file
nano weather_pws.php  
add your node number and the station ID

