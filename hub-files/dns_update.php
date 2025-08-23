#! /bin/bash
# dns_update.service
# 
# sudo systemctl start dns_update.service
# sudo systemctl stop dns_update.service
# sudo systemctl restart dns_update.service
# 
# File modified and turned into a service for Louisiana Image. Its just better  
# v2   12/17/2024  by wrbx288
# v2.1 01/05/2025  Fixed bug in rsync.  Timming for errors fixed.
# v2.2 01/28/2025  Version for hubs     
# v2.3 01/31/2025
version="v2.4"

# Why Use a Service?
# Using a service for background tasks offers several advantages over running a 
# looping program. Services automatically start on boot, ensuring continuous 
# operation without manual intervention. They are managed centrally, with 
# built-in controls for starting, stopping, and restarting. Services also
# offer automatic recovery if they fail, integrated logging, resource management,
# and better security through restricted permissions. Unlike looping programs, 
# services are more reliable, efficient, and easier to manage, making them ideal
# for long-running processes in production environments.
# -------------------------------------------------------------------------------------
# Delay Logic Description:
# 
# This script includes several dynamic delay mechanisms to manage network load 
# and control polling frequency. The delay varies based on the success or failure 
# of fetching the node list and other conditions:
# 
# 1. **Successful Retrieval:**
#    - If the node list is successfully fetched and contains the "extnodes" keyword:
#      - Delay is 180 seconds (3 minutes) under normal operation (`dry_run=0`).
#      - Delay is reduced to 5 seconds in dry run mode (`dry_run=1`).
# 
# 2. **Garbage Response:**
#    - If the node list is fetched but does not contain valid data ("extnodes" missing):
#      - Delay is initially 10 seconds.
#      - After 50 retries, the delay increases to 1 hr to lighten 
#        the network load.
# 
# 3. **Fetch Failure (wget fails):**
#    - If `wget` fails to retrieve the node list:
#      - Delay is 30 seconds for normal retries.
#      - After 50 retries, the delay increases to 1 hr.
#      - If `verbose=1`, the delay is reduced to 5 seconds to allow for quicker testing.
# 
# Key Factors:
# - The `retries` variable increments with each failed attempt or garbage response, 
#   which influences the delay length.
# - The `dry_run` and `verbose` variables override normal delay behavior for testing 
#   or diagnostic purposes.
# 
# The delay logic ensures efficient polling under normal conditions and reduces 
# unnecessary network load during extended failure scenarios.
# -------------------------------------------------------------------------------------
# 
# orginal had no license was located with hamvoip (authour unknown). 
#
TOPDOMAIN=gmrshub.com
SUBDOMAINS="register register"

TMP_FILE="/tmp/dns_tmp.txt"

BACKUP_FILE="/var/log/asterisk/rpt_extnodes.bak"
OUTPUT_FILE="/var/lib/asterisk/rpt_extnodes"
OUTPUT1_FILE="/tmp/rpt_extnodes"

LOGFILE="/var/log/asterisk/dns_log.txt"

# The delay between feteching
MIN_MINUTES=2  # Minimum wait time in minutes
MAX_MINUTES=5  # Maximum wait time in minutes

FILEPATH=/tmp
WGET=`which wget` 
CP=`which cp`
MV=`which mv`
RM=`which rm`
CHMOD=`which chmod`
GREP=`which grep`
CAT=`which cat`
DATE=`which date`
RSYNC=`which rsync`

# Diagnostics
dry_run=0
verbose=1
radio_count=0

_term() {
#  echo "Caught SIGTERM signal!"
  echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z') - Caught termination signal, exiting..." | tee -a $LOGFILE

#  kill -9 $$
  exit 1
}

trap _term SIGTERM
trap _term SIGINT
trap _term SIGPIPE
trap _term SIGHUP
echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z')  =Starting Service= $version" | tee -a $LOGFILE
# This installs a backup file 
#if [ -f "$BACKUP_FILE" ]; then
#    echo "$(date '+%Y-%m-%d %H:%M:%S') - Installing backup file ..." | tee -a $LOGFILE
#    cp "$BACKUP_FILE" $OUTPUT_FILE
#fi

downloads=0
retries=0
while [ 1 ] 
do
  for i in $SUBDOMAINS
  do
    res=0
    while [ $res -eq 0 ]
    do
      #echo "$(date '+%Y-%m-%d %H:%M:%S') - Fetching DNS list from $i.gmrshub.com" | tee -a $LOGFILE
       
      $WGET --timeout=10 --tries=3 -q -O $TMP_FILE "http://registry.gmrshub.com"
#     $WGET -q -O /tmp/rpt_extnodes-temp http://registry.gmrshub.com
      res=$?
      if [ $res -eq 0 ]
      then
#	echo "rubbish" >/tmp/rpt_extnodes-temp
	$GREP -q extnodes $TMP_FILE
    radio_count=$(grep -c 'radio' $TMP_FILE)
	if [ $? -eq 0 ]
	then
		downloads=$((downloads+1))
		retries=0
		if [ $dry_run -eq 0 ]
		then
       		 	$CHMOD 644 $TMP_FILE
#			$CP /tmp/rpt_extnodes-temp $FILEPATH/rpt_extnodes-temp

if [ -f $TMP_FILE ]; then
    cp $TMP_FILE $BACKUP_FILE
    #    sed -i '/radio@[^:]*:\/\//d' $TMP_FILE  # repair blank ips
    #    sed -i '/65000=radio@/d' rpt_extnodes   # kill one node#
    cp $TMP_FILE $OUTPUT1_FILE
    $MV -f $TMP_FILE $OUTPUT_FILE
fi
        
#        echo "$(date '+%Y-%m-%d %H:%M:%S') - Successfully downloaded $downloads" | tee -a $LOGFILE

        
		else
			$CAT $TMP_FILE | tee -a $LOGFILE
		fi
		if [ $verbose -ne 0 ]
		then
        
			echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z') - OK. IP:$radio_count Downloads:$downloads " | tee -a $LOGFILE
		fi
#		if [ $downloads -gt 100 ]
#		then
#			downloads=0
#			sleep 10
			# we dont want to use the rsync at this time
            # $RSYNC -av rsync://sync.gmrshub.com/connect-messages /var/lib/asterisk/sounds/rpt/nodenames  
#			break; # Don't dwell on one server, Look for a new server
#		fi
		if [ $dry_run -eq 0 ]
		then
        
        RANDOM_MINUTES=$(( RANDOM % (MAX_MINUTES - MIN_MINUTES + 1) + MIN_MINUTES ))  # Pick a random minute in range
        SLEEP_TIME=$(( RANDOM_MINUTES * 60 ))  # Convert to seconds
        sleep $SLEEP_TIME
        else
			sleep 15 
		fi
	else
		if [ $verbose -ne 0 ]
		then
        echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z') - Received garbage data, retrying " | tee -a $LOGFILE
 
#			echo "Retreived garbage node list from GMRS Hub"
#			echo "Moving to next node server in list..."	
		fi
		$RM -f $TMP_FILE			
		downloads=0
		retries=$((retries+1))
                if [ $retries -gt 50 ]
       	        then
                echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z') - Pausing to lighten the load downloads:$downloads..." | tee -a $LOGFILE
               	        sleep 45 # doze  to lighten network load
               	else
                echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z') - Sleep 30 downloads:$downloads..." | tee -a $LOGFILE
                       	sleep 30
		fi
		break
	fi
      else
	$RM -f $TMP_FILE
	if [ $verbose -ne 0 ]
	then
            echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z') - ERROR download falure ." | tee -a $LOGFILE
 #       	echo "Problem retrieving node list from GMRS Hub, trying another server";
		downloads=0
		retries=$((retries+1))
	fi
	if [ $verbose -eq 0 ]
	then
		if [ $retries -gt 50 ]
		then
           echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z') - Pausing to lighten the load retries:$retries..." | tee -a $LOGFILE
			sleep 45 # doze to lighten network load
		else
           echo "$(date '+%m-%d-%Y %I:%M:%S-%p-%Z') - Sleep 30 retries:$retries..." | tee -a $LOGFILE
			sleep 30
		fi
	else
		sleep 15
	fi
	break
      fi
    done
 done
done
