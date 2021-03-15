#!/bin/bash

[ -f /home/stat/stat.conf ] && . /home/stat/stat.conf || exit 1

TM=`date -d yesterday +%Y%m`
LOG="$WORKDIR/reports/${TM}.log"

$WORKDIR/trafreport.pl yesterday 0 month >$LOG
cat $LOG | mail -s "${TM} Month traffic report" "${EMAIL_TO}"

rm -f $LOG
