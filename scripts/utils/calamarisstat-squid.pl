#!/usr/bin/perl

#
# call calamaris and send statistics to admin
#

require "/home/stat/config.pl";

$OUTLOG = "$HOMEDIR/tmp/access.log";
$CALAMARIS = "$HOMEDIR/utils/calamaris/calamaris";
$TR = "$HOMEDIR/utils/trim.pl";

$SUBJ = "Proxy-server statistics\nMIME-Version: 1.0\nContent-Language: ru\nContent-Type: text/html; charset=utf-8";

system ("cat $OUTLOG | $TR | $CALAMARIS -a -F html | mail -s \"$SUBJ\" $Admin_email");

exit 0;

	