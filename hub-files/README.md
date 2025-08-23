GMRS HUB server files for creating a better hub.


astdb.php is a drop in replacement.   IT corrects the NULL problems on boot up
by storing old copies and only updating if they are valid. It is desgined
to fix many problems that have happned i the past like servers going down 
or just corrupted nodelist being distributed. This fixes it all. 

It also stops users from entering bad data in the nodelist as well as trims
lines that are to long for the supermon display while allowing through emotocons.

It will also create more than one nodelist one with only hubs as well as a unmodified list and a backup list

More later........
