# gmrslive.com Time Temp Weather Replacement PROGRAM v2

This software replaces the time and temp scripts and the warning scripts.
It upgrades them to the new weather service servers with the new API. 
It also adds detection for cpu temp and more.
Written from scratch in PHP no shell scripts.

This software uses a NWS licensed API code 

download the installer.

Drop to a shell
type

sudo wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install.php

run the installer

php install.php


This will install and place you in the setup program.


The software is a custom time and temp system that reads data from 
mesowest, madis, APRSWXNET/Citizen Weather Observer Program (CWOP)
It allows you to pick a station closest to you. 
find your local MADIS station and airport go to the map 

https://madis-data.ncep.noaa.gov/MadisSurface/ Get the ID from this map only

http://www.wxqa.com/  (CWOP) main website

https://mesowest.utah.edu/

https://madis-data.ncep.noaa.gov

https://aprs.fi/ These are aprs stations but you cant use station numbers from this map.

  
Select how much data you want only temp or temp hum rain wind. The temp data is more accurate than the 
stock script which does not allow you to pick the station. in addition this allows station owners 
like me and hams running CWOP stations to use your own local temp.

run them

php weather_pws.php

php cap_warn.php

Updates 7/14/2023

Detects if your registered and fixes it.
Detects if your bridiging networks and warns you then removes bridge.

Supermon 1 click install.

Supermon mods for GMRS

New GMRSLive node repeater hub directory
















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


Reg fix.

This version has auto reg detection and if you want will run the reg fix to automaticaly place you back online.
I created this script to get arround a problem with my isp ATT FIXED WIRELESS. After a while a day or 2 you become unregestered
and rebooting will not solve the problem. This fixes that by rotating the port then placing it back on the next boot.
Beware dv switch wont be able to connect to you node while the port is not standard.



