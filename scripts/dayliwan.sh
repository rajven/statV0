#!/bin/bash

[ -f /home/stat/stat.conf ] && . /home/stat/stat.conf || exit 1

TM=`date -d yesterday +%Y%m%d`
LOG="$WORKDIR/reports/${TM}.log"

$WORKDIR/trafreport.pl yesterday >$LOG
cat $LOG |  mail -s "${TM} dayli traffic" "${EMAIL_TO}"

rm -f $LOG
