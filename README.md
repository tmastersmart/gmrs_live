# gmrslive.com GMRS NODE Manager

=====================================================================

This software was written for GMRS nodes.  

* New time temp weather system
* Weather Alert system based on New API
* Supermon 1 click install
* Setup screen. No editing files.
* Supermon new weather forcast 
* Supermon repeater and Hub index
* Bridging notification and autofix 
* Reg Falure notification and autofix
* Network Falure notification
* High CPU Temp Alarm
* CPU event alarms 
* Alarms/Notificationover read by the node lady
* Supermon Logbook
* Uninstaller and Updater.

download the installer.
Drop to a shell
type
sudo wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install.php

This in not a bunch of shell scripts nor are they modifications to any existing scripts
This is a totaly new program written from the ground up in Louisiana in cross platform PHP.

The main goal is to modernise and get rid of editing files for new users. You will see a new
option in your menu when you logon to the node this will take you to the setup program.

This is only the start I am creating a GMRS Supermon and in that there will be admin
screens so that you will never have to login to make changes.

Will this work on a repeater. Yes I am writting this for my node and upcomming repeator thats why
everything is optional you may disable anything you dont want.


----- Modules that are called by cron or manualy -----

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
 
 Network notification. No longer will you node go down and you not know about it.
 
 Advanced time system is much better than the default scripts. Includes randomised 
 voices and comments to make it sound more real.
 

Run the program by typing 

php weather_pws.php


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


Be sure you remove all links to AUTOSKY if you were using it.
Setup will atempt to do this for you.

Remove the start file that places the looping script in memory 
Edit /etc/rc.local file.
remove
/usr/local/bin/AUTOSKY/AutoSky
/usr/local/bin/AUTOSKY/AutoSky.ON

Test the program by typing php cap_warn.php from the directory

* nodelist_process.php

The nodelist processor creates a cleaned up csv filtered nodelist database
This creates a database for the GMRS Supermon node index to use.
This processor runs at a random time at night with retries at about 1 hr apart.  
On each run it checks the nodelist to see if it was updated if so it does nothing.
If the nodelist is out of date it makes atempts to update it. 
You can force a update by running it manualy. Extra care was taken to use random 
Times as not to overload servers. The update times are subject to change as I watch how it works.



* Software is installed into /etc/asterisk/local/mm-software/
drop to a shell and type
cd /etc/asterisk/local/mm-software

type any of the following

php setup.php
php temp.php
php cap_warn.php
php weather_pws.php
php nodelist_process.php



* licensing
Weather scripts use keys specialy created for this software licensed to me.
You may not use these keys in other software get your own.

All software is written by me.
Execpt for the mods to link.php
link.php is licensed under the GNU General Public License v3.0
https://www.gnu.org/licenses/gpl-3.0.en.html

You must have my permission to redistribute this software package. 
With I plan on granting I just need to list who is doing it thanks..... 



 
* new updates

The setup program can update and uninstall.

If you have any problems and need to uninstall please report them to me at www.lagmrs.com




