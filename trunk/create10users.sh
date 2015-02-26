#!/bin/bash
# A simple script to create 10 users in ascending numberic order using the symfony console
# and the FOSUserBundle. 
# Written by: Paul
# Last updated: Nov 14, 2013
# ----------------------------------------------
[ $# -lt 3 ] && { echo -e "Usage: $0 username @email.com password firstname lastname city country\nEg) $0 test @imdc.ca secretpassword John Smith Toronto Canada\nThis creates users with loginnames test1, test2, test3, .. and email's test1@imdc.ca test2@imdc.ca test3@imdc.ca ..."; exit 1; }



CMDSTRING="php app/console imdc:user:create"

USERNAME=$1
EMAIL=$2
PASSWORD=$3
FIRSTNAME=$4
LASTNAME=$5
CITY=$6
COUNTRY=$7

for i in {1..10}
do
	echo $CMDSTRING $USERNAME$i $USERNAME$i$EMAIL $PASSWORD $FIRSTNAME $LASTNAME $CITY $COUNTRY
	$CMDSTRING $USERNAME$i $USERNAME$i$EMAIL $PASSWORD $FIRSTNAME $LASTNAME $CITY $COUNTRY
done
