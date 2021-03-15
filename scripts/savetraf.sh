#!/bin/sh
#
# archive daily traffic
#

[ -f /home/stat/stat.conf ] && . /home/stat/stat.conf || exit 1

TM=`date -d yesterday +%Y%m%d%a`

FNAME=$WORKDIR/traf/$TM.tgz

if [ -f $FNAME ]; then
    FNAME=$WORKDIR/traf/${TM}-1.tgz
    fi

# tar ulog traffic    
cd $WORKDIR/current/
nice -19 tar -c -z --remove-files  -f $FNAME   2*

# get squid traffic for all day
#rm -f $WORKDIR/tmp/access.log
#cat $WORKDIR/squid/* >>$WORKDIR/tmp/access.log

# tar squid traffic
#cd $WORKDIR/squid/
#nice -19 tar c -z --remove-files  -f $FSNAME   2*

# analyze squid traffic
#$WORKDIR/utils/calamarisstat-squid.pl
