#!/bin/bash
[ -z "${1}" ] && exit 1

TYPE=${1}
MAC=${2}
IP=${3}
NAME=${4}

EXISTS=`cat /etc/dnsmasq.d/userlist | grep -i "$MAC" | wc -l`

[ $EXISTS -ne 0 ] && exit 0

. /home/stat/stat.conf

mysql -h $DBSERVER -u $DBUSER --password=$DBPASS -D rstat -N < $WORKDIR/mysql/userlist.sql >$WORKDIR/userlist

EXISTS=`cat $WORKDIR/userlist | grep -i "$IP" | wc -l`

[ $EXISTS -ne 0 ] && exit 0

echo "INSERT INTO User_auth (IP,userid,mac,comments,proxy,nat,enabled) VALUES (\"$IP\",\"341\",\"$MAC\",\"$NAME\",\"1\",\"1\",\"1\");" | mysql -h $DBSERVER -u $DBUSER --password=$DBPASS -D rstat -N
echo "type: $TYPE INSERT INTO User_auth (IP,userid,mac,comments) VALUES (\"$IP\",\"341\",\"$MAC\",\"$NAME\",\"1\",\"1\",\"1\");" >> $WORKDIR/reports/dhcp.log

exit 0
