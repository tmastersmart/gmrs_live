# gmrslive.com scripts


A collection of scripts that will work on gmrslive nodes

download the installer.

ch /etc/asterisk/local/

sudo wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install.php

run the custom installer

php install.php

ch /etc/asterisk/local/mm-software


load the setup program
php setup.php


you can now add it to cron

crontab -e add the following for time on the hr between 6am and 11pm

at 30 mins on the hr to prevent overrun with time.

30 7-23 * * * php /etc/asterisk/local/temp.php >> /dev/null

weather_pws.php

This is a custom time and temp system that reads data from 
mesowest, madis, APRSWXNET/Citizen Weather Observer Program (CWOP)
It allows you to pick a station closest to you. 
find your local MADIS station and airport go to the map 

https://madis-data.ncep.noaa.gov/MadisSurface/ Get the ID from this map only

http://www.wxqa.com/  (CWOP) main website

https://mesowest.utah.edu/

https://madis-data.ncep.noaa.gov

https://aprs.fi/ These are aprs stations but you cant use station numbers from this map.

  

add your station ID. Select how much data you want only temp or temp hum rain wind. 
The temp data is more accurate than the stock script which does not allow you to pick the station.
in addition this allows station owners like me and hams running CWOP stations to use your own local temp.

run them

php weather_pws.php

php skywarn.php

I have my cron setup like this.


#00 8-23 * * * (source /usr/local/etc/allstar.env ; /usr/bin/nice -19 /usr/bin/perl /usr/local/sbin/saytime.p$

#*/20 * * * * /usr/local/bin/AUTOSKY/AutoSky

*/15 * * * * php /etc/asterisk/local/mm-software/skywarn.php >> /dev/null

00 7-23 * * * php /etc/asterisk/local/mm-software/weather_pws.php >> /dev/null

the weather_pws.php has the temp warning built into it.

super mon. Read supermon.txt file you need to change some code.


Aditional scripts not in the installer

weather_hubitat.php

This script is for hubitat smart hub owners it pulls the temp from a sensor on your hub. See instructions in script

port_rotate.php

I created this script to get arround a problem with my isp ATT FIXED WIRELESS. After a while a day or 2 you become unregestered
and rebooting will not solve the problem. I have discovered that changing the port will allow you to register, this
script changes you port to a random number. You set a range in the script. You can run as you like I do it once a day. 
The port wont actualy cange until asterisk is restarted. This is a beta script Im working on a automated script.
If your node works for days then stops. Try running this script then reboot.



