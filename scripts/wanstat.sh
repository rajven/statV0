#!/bin/sh
#
# make traffic statistics snapshot


TM=`date +%Y%m%d-%H%M`

[ -f /home/stat/stat.conf ] && . /home/stat/stat.conf || exit 1

# capture squid log
#cp /var/log/squid/access.log $WORKDIR/squid/$TM --backup --suffix="-`date +%s`" -f
#cat /dev/null > /var/log/squid/access.log

#chown stat:stat $WORKDIR/squid/$TM
#cat $WORKDIR/squid/$TM >> $WORKDIR/current1/current-squid
#chown stat:stat $WORKDIR/current1/current-squid

# create squid statistics
#cat $WORKDIR/current1/current-squid | $WORKDIR/parse_squid.pl
#rm -f $WORKDIR/current1/current-squid

###skill -HUP -c ulog-acctd
skill -TSTP -c ulog-acctd
mv /var/log/ulog-acctd/account.log $WORKDIR/current/$TM --backup --suffix="-`date +%s`" -f
skill -CONT -c ulog-acctd

chown stat:stat $WORKDIR/current/$TM
cat $WORKDIR/current/$TM >> $WORKDIR/current1/current
chown stat:stat $WORKDIR/current1/current

# create statistics
cat $WORKDIR/current1/current | $WORKDIR/parse_ulog.pl

rm -f $WORKDIR/current1/current

$WORKDIR/recheck.sh &

exit
