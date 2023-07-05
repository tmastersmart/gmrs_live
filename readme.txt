
This software was written for GMRS nodes.  It contains the following modules. 
All written from scratch in PHP

* weather_pws.php Time and temp & weather
 
  I wrote this because the built in temp was not displaying the correct temp for
my area. I have my own weather station that submits data to the COWP which checks
and relays this data into the NWS system. Most all services use this for my city
but the node was not. This module pulls data from mesowest, madis, APRSWXNET/
Citizen Weather Observer Program (CWOP). APRSWXNET is the ham radio weather 
stations CWOP is the stations that submit through the net like mine.
 
  Find your local MADIS station and airport go to the map 
https://madis-data.ncep.noaa.gov/MadisSurface/  make sure all DATASETS are turned
on and find the code your station or a close station near you.

  
  I am using the new NWS API for forecast and alerts.  
  
  The module also has a high temp warning notice that will play after the weather. 
  It can warn of over temp and throttling of the CPU.

 Also included is a watchdog checking to see if net goes down and if you become unregistered.
 The automated reg fix will atempt to bring you back online. This wont work for everyone
 because it depends on why your unregistered. If your port is being blocked by your
 router, modem, isp or gateway after several days of use it will fix it.
 

Run the program by typing 

php weather_pws.php


Replace the time in cron
(source /usr/local/etc/allstar.env ; /usr/bin/nice -19 /usr/bin/perl /usr/local/sbin/saytime.p$

with this 
00 * * * * php /etc/asterisk/local/mm-software/weather_pws.php >/dev/null

* temp.php

 It will play High temp messages and any cpu code thrown including throttling.
For debugging you can have it say the current temp or
just talk when its over heating. Run it by typing 

php temp.php

You can run this by cron if you like but its built into the time and temp script also.
If you don't want to use time and temp you can just use temp.


* cap_warn.php
 The current alert script is way to hard to setup and most people are not even
using it. When they do it doesn't work right if not set up correctly. This is
my own version written from scratch in PHP. Its using the same sound files at
this time. Also the existing script uses a looping program that stays 
running in memory all the time. I do not like that I think its better to run 
it from cron on a schedule so its only in memory when it runs.

The second problem is the NWS is moving away from v1.1 cap to v1.2 and autosky will
stop working when its changed. I am using the NWS new API.
The new API is geocoded so you need to enter your LAT/LON

The program is backwards compatible with the autosky tailfile.
Supermon will have to be modified to read the new text files for alerts and forcast

edit rpt.conf to include this use , to add more than one file.
tailmessagelist=/tmp/AUTOSKY/WXA/wx-tail

Remove all other autosky settings and stop the looping script from running.

Edit cron and change

*/4 * * * * /usr/local/bin/AUTOSKY/AutoSky
to 
*/15 * * * * php /etc/asterisk/local/mm-software/skywarn.php >/dev/null

Use what ever time cycle you want. I prefer a longer cycle which keeps processes down.

Remove the start file that places the looping script in memory 
Edit /etc/rc.local file.
remove
/usr/local/bin/AUTOSKY/AutoSky
/usr/local/bin/AUTOSKY/AutoSky.ON

To test the alerts look for a feed on the alerts.weather.gov page with an alert
and enter that code in setup.

Run the program by typing ?php skywarn.php?


* config.php

 One of my main goals is to make it easer for new users who have never used a PI
before to edit things. The editor program solves that while allowing me to 
provide ongoing updates without requiring manual edits to files.


When running these programs from the command line you must enter 

php first then the program. 


 
 





