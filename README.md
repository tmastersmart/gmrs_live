# GMRSHUB Louisiana Node Image 

=====================================================================

update:9/4/25

This is now the Louisiana Node Image for GMRSHUB

Old files removed. This archive will only hold the image and any upgrades.



This software was written for GMRShub nodes.  

* New time temp weather system
* Weather Alert system based on New API
* Weather temp system for NWS or Ambent Weather
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

Only installable as a full PI image

Download will be here and on mediafire
This is a active production image and there will be updates unlike others
that get frozen with bugs. If see something does not work i need to know
so I can fix it use the chat link at lagmrs.com 

There are 2 download streams you can use just to be sure that its always
online. 
1) https://github.com/tmastersmart/gmrs_live/releases
2) https://www.mediafire.com/folder/q6z3i8plwwily/gmrs

---- new description -----


Louisiana GMRS Image

This document outlines the significant changes made to the GMRS image 
to create the Louisiana Image, a customized and enhanced version 
tailored for specific use cases. The modifications include performance 
improvements, added functionality, and increased system reliability.

1. File System and Storage Enhancements

Enabled fstrim Service:
Configured fstrim.timer to run monthly, ensuring periodic trimming of 
unused blocks for improved SD card longevity.

Added Swap Space:
Implemented a swap file to enhance memory management, particularly 
under heavy loads.

Automatic SD Card Size Expansion:
Automated resizing of partitions to utilize the full capacity of the 
SD card during initial boot.

2. Logging and Backup Improvements

Log Rotation:
Configured log rotation to prevent excessive disk usage by log files.

Automated Backup and Repair:
Added a system for scheduled backups and automatic repair scripts to 
minimize downtime and ensure data integrity.

3. New Features and Services

Preinstalled DVSwitch:
Integrated DVSwitch suite for seamless digital voice operations.

Enhanced Status Page:
Redesigned the status page with additional features and real-time 
system monitoring.

Private Nodes for 4-Digit Node Numbers:
Support for private GMRS nodes using a 4-digit numbering system.

Fan Control Service:
Added a service to dynamically control cooling fans based on CPU 
temperature.

Hi CPU Temp Alarms:
Configured alarm notifications for high CPU temperature events.

4. Network and Connectivity Enhancements

Dynamic DNS Update Service:

Created a service to automatically update DNS entries for the node.

Nodelist Downloader:

Implemented a caching mechanism to ensure the nodelist is always 
available, mitigating issues with missing data.

UPnP Support:

Automates the process of opening port 80 for HTTP traffic on your
router using Universal Plug and Play (UPnP). Port 80 is commonly 
used for web servers, and by forwarding this port, you can make a
web server running on your Raspberry Pi accessible from outside
your local network.


5. Weather Monitoring and Emergency Alerts

Time and Temperature Rewrite:

Completely revamped the time and temperature reporting system for 
improved accuracy and functionality.
The system now utilizes GPS positioning instead of location codes, 
ensuring more precise and localized storm alerting.
Support for weather stations using the MADIS system as well as Ambent 
Weather personal weather stations.

NWS CAP Weather Alerts Integration:

Utilized the National Weather Service's Common Alerting Protocol (CAP) 
system for detailed and timely weather alerts.

GMRS Skywarn Replacement:

Developed a GMRS-based replacement for Skywarn, enabling efficient 
weather monitoring and emergency communication based on GPS location, 
not county codes.

6. Web-Based System Management

Webmin Integration:

Installed Optional Webmin, a web-based system administration tool that
provides an intuitive interface for managing various aspects of the system. 
With Webmin, users can easily configure system settings, manage 
services, monitor performance, and perform routine maintenance tasks 
without needing to access the command line. This addition simplifies 
system management for both novice and advanced users.

These enhancements collectively create a robust, feature-rich 
environment tailored for GMRS, ensuring reliable operation, enhanced 
functionality, and improved user experience.

7. Alarms for moble usage 

Moble support:

Temp alarms will now be given if the node overheats. Also if you internet 
fails you will receive a internet down alarm. If you turn on support for
connection monitoring you will get a alarm if your not connected to a hub.

8. Upgradable

This image is upgradable you will never have to change images again. As
improvements are made or bug fixes you just need to run the update. 

9. Avahi mDNS Support
This system includes Avahi, which enables multicast DNS (mDNS) resolution.
With mDNS, devices on the local network can be accessed using the .local
domain instead of needing to remember IP addresses.

For example, you can access this node using:
gmrsnode10000.local   
Each node will have its own local domain name based on its node number.

Avahi is now off by default because it wont work with my hotspot.
Use only on routers.  A new menu has been added to turn it on and off.

10. Simple Tune USB menu update.
Corrections to the menu interface to make it clear which settings are
for transmit to the net and which is for your MIC adjustments.
'simpleusb-tune-menu'  or 'simpleusb-tune-menu-bak' for the old version

11. Installed fake hardware clock.
fake-hwclock is a utility used on PIs OS because they have no hardware clock.
Its function is to save the system's time & date to a file and restore it
upon boot. On the node this will prevent time from going out of sync if
it reboots with the network down.

12. Can update words on the fly with a voices key https://voicerss.org

13. Now has a jingle system on the botom of the area to play your custom
messages.  key will be need to do customs https://voicerss.org






