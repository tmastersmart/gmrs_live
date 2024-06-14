# gmrslive.com GMRS NODE Manager
# GMRS Supermon

=====================================================================

update:6/14/24 The installer has been updated of you had problems try the new one.

This software was written for GMRS nodes.  

* New time temp weather system
* Weather Alert system based on New API
* Weather temp system for NWS or Ambent Weather
* Supermon 1 click install
* DVSwitch 1 click install
* Setup Program in admin menu. No editing files.
* Supermon new weather forcast 
* Supermon repeater and Hub index
* Bridging notification and autofix 
* Reg Falure notification and autofix
* Network Falure notification
* High CPU Temp Alarm
* CPU event alarms 
* Uninstaller and Updater.
* Totaly new nodelist updater
* New bootup system gives IP,Reg status and CPU temp

download the installer.

Drop to a shell

type
cd /tmp

sudo wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install.php

php install.php


This in not a bunch of shell scripts nor are they modifications to any existing scripts
This is a totaly new program written from the ground up in Louisiana in cross platform PHP.

The main goal is to modernise and get rid of editing files for new users. You will see a new
option in your menu when you logon to the node this will take you to the setup program.

This is only the start I am creating a GMRS Supermon and in that there will be admin
screens so that you will never have to login to make changes.

Will this work on a repeater. Yes I am writting this for my node and upcomming repeator thats why
everything is optional you may disable anything you dont want.


  Find your local MADIS station and airport go to the map 
https://madis-data.ncep.noaa.gov/MadisSurface/  make sure all DATASETS are turned
on and find the code your station or a close station near you.

 To add ambent weather stations get a key from your weather station web page.
  

 
Be sure that you are not running AUTOSKY.
Remove the start file that places the looping script in memory 
Edit /etc/rc.local file.
remove
/usr/local/bin/AUTOSKY/AutoSky
/usr/local/bin/AUTOSKY/AutoSky.ON





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
You must have my permission to redistribute this software package. 
With I plan on granting I just need to list who is doing it thanks..... 



supermon files which are in a seperate directory
and are licensed under the GNU General Public License v3.0
https://www.gnu.org/licenses/gpl-3.0.en.html




 
* new updates

There was a bug fix on the installer the I)option worked but was missing from the menu. No one told me? So it took me a while to find it.
DVSWITCH setup has been moved into its own module so it can be standalone.  It will install with the node manager or it can be installed by itsself.

DVSWITCH setup tool seperate installer. (use only if your not installing node manager above)

Project now has its own webpage here https://www.lagmrs.com/dvswitch_setup.php

type
cd /tmp

wget https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/install_dvswitch.php

php install_dvswitch.php




