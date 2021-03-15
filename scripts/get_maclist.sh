#!/bin/bash

. /home/stat/stat.conf

TMP1=`mktemp -t userlist.XXXXXXXXXX`

$WORKDIR/get_maclist.pl "$TMP1" >/dev/null
RET=$?

[ ${RET} -ne 0 ] && {
    rm -f "$TMP1"
    exit
    }

/usr/bin/diff -aqbBfi /etc/dnsmasq.d/userlist "$TMP1" >/dev/null
RET=$?

[ ${RET} -eq 0 ] && {
    rm -f "$TMP1"
    exit
    }

cat "${TMP1}" > /etc/dnsmasq.d/userlist
cat /dev/null > /var/lib/misc/dnsmasq.leases

rm -f "${TMP1}"

service dnsmasq restart >/dev/null

exit
