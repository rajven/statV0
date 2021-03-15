#!/bin/bash

CONFIG="/home/stat/shapers.config"

[ -f ${CONFIG} ] && . ${CONFIG} || { echo "Config file ${CONFIG} not found!"
    exit 1
    }

check_run
create_lock

[ "$1" == "clear" ] && {
    log_info "Remove all shapers..."
    clear_all
    remove_lock
    do_exit 0
    }

[ "$1" == "sync" ] && {
    log_info "Sync all shapers..."
    sync_classes
    recreate_iptables
    remove_lock
    do_exit 0
    }

[ "$1" == "recreate" ] && {
    log_info "Create all shapers..."
    cat $WORKDIR/users >$WORKDIR/users.new
    clear_all
    }

do_exec "$MYSQL -h $DBSERVER -u $DBUSER --password=$DBPASS -D rstat -N < $WORKDIR/mysql/shaperlist.sql >> $WORKDIR/users.new"

[ ! -e $WORKDIR/users.new ] && {
    log_error "New shaper list ($WORKDIR/users.new) not found! Aborting..."
    remove_lock
    do_exit 1
    }

[ ! -s $WORKDIR/users.new ] && {
    log_error "Config file $WORKDIR/users.new is empty. Clear shapers and exit."
    clear_all
    remove_lock
    log_debug "OK. Exit."
    do_exit 0
    }

#check what current shaper list exist
[ ! -e $WORKDIR/users ] && {
    log_error "Current user config not found!"
    do_exec "touch $WORKDIR/users"
    }

check_shapers
#sync_classes

[ "${SHAPER_ERR}" == "2" ] && recreate_iptables

NEWLIST=`diff -abBfi $WORKDIR/users $WORKDIR/users.new 2>/dev/null | egrep "[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}" | awk '{ print $1 }'`
DELLIST=`diff -abBfi $WORKDIR/users.new $WORKDIR/users 2>/dev/null | egrep "[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}" | awk '{ print $1 }'`

log_info "New shapers for adding:"
[ -z "${NEWLIST}" ] && log_info "none" || log_info "${NEWLIST}"
log_info "Old shapers for removing:"
[ -z "${DELLIST}" ] && log_info "none" || log_info "${DELLIST}"

### remove old shapers
TMP1=`mktemp -t shaper.XXXXXXXXXX`
TMP2=`mktemp -t shaper.XXXXXXXXXX`

echo "${NEWLIST}" > $TMP2
echo "${DELLIST}" >> $TMP2
NEWLIST=`cat $TMP2 | sort --numeric-sort --reverse | uniq`
do_exec "rm -f $TMP2 >/dev/null 2>&1"

do_exec "touch ${SHAPER_LIST}"

log_info "Remove old shapers"
echo "${DELLIST}" | while read USER_ID; do
[ -z "${USER_ID}" ] && continue
SHAPER_DATA=`cat ${SHAPER_LIST} | grep "^$USER_ID[[:space:]]"`

echo "${SHAPER_DATA}" | while read ID IP_SRC IP_DST CLASS_LAN CLASS_WAN DEV_DST BANDWIDTH; do
[ -z "$ID" ] && continue
log_info "Remove shaper with ID: $ID Source: ${IP_SRC} Dest: ${IP_DST} Class_lan: $CLASS_LAN Class_wan: $CLASS_WAN DEV: $DEV_DST BAND: ${BANDWIDTH}"
[ "$CLASS_LAN" != "0" ] && del_class_ipt $DEV_DST $CLASS_LAN $IP_SRC $IP_DST
[ "$CLASS_WAN" != "0" ] && del_class_ipt $WAN $CLASS_WAN $IP_DST $IP_SRC
do_exec "cat ${SHAPER_LIST} | grep -v \"^$USER_ID[[:space:]]$IP_SRC[[:space:]]$IP_DST[[:space:]]\" > ${TMP1}"
# remove class list
do_exec "cat ${TMP1} > ${SHAPER_LIST}"
done
done

do_exec "rm -f ${TMP1} >/dev/null 2>&1"

### add new shapers
log_info "Add new shapers"

check_init ${WAN}
[ "${ETH_INIT}" == "0" ] && init_shaper ${WAN}

echo "${NEWLIST}" | while read USER_ID; do
[ -z "${USER_ID}" ] && continue

SHAPER_DATA=`cat $WORKDIR/users.new | grep "^$USER_ID[[:space:]]"`
FIRST_STEP="1"

echo "${SHAPER_DATA}" | while read ID IP_SRC IP_DST BANDWIDTH; do
#lan shaper

# get interface 
VIA=`/sbin/ip r get ${IP_DST} 2>/dev/null | grep via`
DST_DEV=
[ -n "${VIA}" ] && DST_DEV=`/sbin/ip r get ${IP_DST} 2>/dev/null | grep dev | awk '{ print $5 }'` || {
    LOC=`/sbin/ip r get ${IP_DST} 2>/dev/null | grep broadcast`
    [ -n "${LOC}" ] && DST_DEV=`/sbin/ip r get ${IP_DST} 2>/dev/null | grep dev | awk '{ print $4 }'` || DST_DEV=`/sbin/ip r get ${IP_DST} 2>/dev/null | grep dev | awk '{ print $3 }'`
    }

[ "$DST_DEV" == "$WAN" ] && {
	log_info "${IP_DST} find at wan interface! Skipped..."
	continue
	}

[ -z "${DST_DEV}" ] && {
	log_error "${IP_DST} - network is unreachable! Skipped..."
	continue
	}

OK=`echo "${DST_DEV}" | grep "^eth" | wc -l`

[ $OK -eq 0 ] && {
	log_error "${IP_DST} - network is unreachable! Skipped..."
	continue
	}

check_init "${DST_DEV}"
[ "${ETH_INIT}" == "0" ] && init_shaper "${DST_DEV}"

[ -n "$FIRST_STEP" -o -z "$NEWCLASSID_LAN" ] && {
	# get max classid from current class list 
	MAXCLASSID_LAN=`$TC class show dev ${DST_DEV} | grep parent | awk '{ print $3 }' | awk -F: '{ print $2 }' | sort --numeric-sort | tail -1`
	# if class list is empty - set default value - else get next value 
	[ -z "${MAXCLASSID_LAN}" ] && MAXCLASSID_LAN="10"
	NEWCLASSID_LAN=`expr $MAXCLASSID_LAN + 1`
	# add class to interface
	add_class_ipt $DST_DEV $NEWCLASSID_LAN $BANDWIDTH
	do_exec "$IPTABLES -t mangle -I _SHAPER_IN -s $IP_SRC -d $IP_DST -j MARK --set-mark $NEWCLASSID_LAN"
	echo "-I _SHAPER_IN -s $IP_SRC -d $IP_DST -j MARK --set-mark $NEWCLASSID_LAN" >>$IPTABLES_SHAPER
	}

#for wan 
[ "${IP_SRC}" == "0/0" ] && {
	# get max classid from current class list 
	MAXCLASSID_WAN=`$TC class show dev ${WAN} | grep parent | awk '{ print $3 }' | awk -F: '{ print $2 }' | sort --numeric-sort | tail -1`
	# if class list is empty - set default value - else get next value 
	[ -n "$FIRST_STEP" -o -z "$NEWCLASSID_WAN" ] && {
		[ -z "${MAXCLASSID_WAN}" ] && MAXCLASSID_WAN="10"
		NEWCLASSID_WAN=`expr $MAXCLASSID_WAN + 1`
		# add class to interface 
		add_class_ipt $WAN $NEWCLASSID_WAN $BANDWIDTH
		}
	do_exec "$IPTABLES -t mangle -I _SHAPER_OUT -d $IP_SRC -s $IP_DST -j MARK --set-mark $NEWCLASSID_WAN"
	echo "-I _SHAPER_OUT -d $IP_SRC -s $IP_DST -j MARK --set-mark $NEWCLASSID_WAN" >>$IPTABLES_SHAPER
	}

[ -n "$NEWCLASSID_WAN" ] && echo "$USER_ID ${IP_SRC} ${IP_DST} ${NEWCLASSID_LAN} ${NEWCLASSID_WAN} ${DST_DEV} ${BANDWIDTH}" >>${SHAPER_LIST}
[ -z "$NEWCLASSID_WAN" ] && echo "$USER_ID ${IP_SRC} ${IP_DST} ${NEWCLASSID_LAN} 0 ${DST_DEV} ${BANDWIDTH}" >>${SHAPER_LIST}
log_info "ID: $USER_ID IP_SRC: ${IP_SRC} IP_DST: ${IP_DST} Bandwidth: ${BANDWIDTH}Kbit CLASSES: ${NEWCLASSID_LAN} ${NEWCLASSID_WAN} DEV: ${DST_DEV}"
FIRST_STEP=
done
done

do_exec "rm -f $WORKDIR/users"
do_exec "mv $WORKDIR/users.new $WORKDIR/users"

remove_lock
log_info "OK. Exit."

do_exit 0
