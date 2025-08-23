GMRS HUB server files for creating a better hub.


astdb.php is a drop in replacement.   IT corrects the NULL problems on boot up
by storing old copies and only updating if they are valid. It is desgined
to fix many problems that have happned i the past like servers going down 
or just corrupted nodelist being distributed. This fixes it all. 

It also stops users from entering bad data in the nodelist as well as trims
lines that are to long for the supermon display while allowing through emotocons.

It will also create more than one nodelist one with only hubs as well as a unmodified list and a backup list

This is to be called from cron as astdb.php cron.
You may call it several times a day it wont actualy update unless it needs a new file unlike the orginal.
More later........


/usr/local/sbin/dns-update.sh
DNS registry file updater with logs. This replaces the ast system wrongly called nodelist????? 
why i dont know its a registery dns system not a nodelist.    

This is a new service that runs in the background not needing cron to process
the registry and install it also creating a backup.  
It has many improvements over the current system
along with a error log so you can see whats going on. 
You will need the install file and the php file. 

You also need to stop the current nodelist (misnamed) ast service so it wont run.



install 
curl -sL https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/hub-files/install-gmrs-dns-update.sh | sudo bash

Or if you dont want to run it from github. and want to look at it before it runs do this.

cd /tmp
curl -sLO https://raw.githubusercontent.com/tmastersmart/gmrs_live/main/hub-files/install-gmrs-dns-update.sh
sudo bash install-gmrs-dns-update.sh


