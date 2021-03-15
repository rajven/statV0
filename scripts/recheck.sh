#!/bin/sh
#
# make user acl

#exit

[ -z `pgrep mysqld_safe` ] && exit 100

[ -f /home/stat/stat.conf ] && . /home/stat/stat.conf || exit 1

TM=`date +%Y%m%d-%H%M`

check_run
create_lock

renice +19 -p $$ >/dev/null 2>&1

# check quotes
do_exec "$WORKDIR/checkquotes.pl >> $WORKDIR/reports/quotes.log"

renice -5 -p $$ >/dev/null 2>&1
# create iptables rules
do_exec "$WORKDIR/fire-libiptc.pl > /dev/null 2>&1"

renice +19 -p $$ >/dev/null 2>&1

# update shaper
#do_exec "$WORKDIR/updateshapers.sh >/dev/null 2>&1"

# update dnsmasq userlist
$WORKDIR/get_maclist.sh >/dev/null 2>&1

remove_lock

do_exit 0
