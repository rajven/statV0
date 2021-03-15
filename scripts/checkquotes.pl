#!/usr/bin/perl  

use DBI;
use Time::Local;
use FileHandle;
require "/home/stat/config.pl";

$mailbody = "";

### get time 
($hour,$mday,$mon,$year) = (localtime())[2,3,4,5];
$mon += 1;
$year += 1900;
$workdate = localtime();

### globals

###
# Create new database handle. If we can't connect, die()
$dbh = DBI->connect("dbi:mysql:database=$DBNAME;host=$DBHOST","$DBUSER","$DBPASS");
if ( !defined $dbh ) {    die "Cannot connect to mySQL server: $DBI::errstr\n"; }

#get userid list
$sth = $dbh->prepare( "SELECT Login, perhour, perday, permonth, email, blocked, id  FROM User_list where User_list.deleted=0");

if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;

# user auth list - read first - before clear block status!!!
my $authlist_ref = $sth->fetchall_arrayref();

# get current user traffic

$HourQuotes = "Select (SUM(tin))/1048576 as wit, User_list.id
FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout
from User_stats
where (YEAR(`Dat`)=$year and MONTH(`Dat`)=$mon and DAY(`Dat`)=$mday and HOUR(`Dat`)=$hour)
GROUP by userid) As V, User_auth, User_list
WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id)
GROUP by User_list.id
";

$DayQuotes = "Select (SUM(tin))/1048576 as wit, User_list.id 
FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout
from User_stats
where (YEAR(`Dat`)=$year and MONTH(`Dat`)=$mon and DAY(`Dat`)=$mday)
GROUP by userid) As V, User_auth, User_list
WHERE (V.userid=User_auth.id)
and (User_auth.userid=User_list.id)
GROUP by User_list.id
";

$MonthQuotes = "Select (SUM(tin))/1048576 as wit, User_list.id
FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout
from User_stats
where (YEAR(`Dat`)=$year and MONTH(`Dat`)=$mon)
GROUP by userid) As V, User_auth, User_list
WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) 
GROUP by User_list.id
";

$sth = $dbh->prepare($HourQuotes);
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;
my $hour_q = $sth->fetchall_arrayref();

$sth = $dbh->prepare($DayQuotes);
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;
my $day_q = $sth->fetchall_arrayref();

$sth = $dbh->prepare($MonthQuotes);
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;
my $month_q = $sth->fetchall_arrayref();

#clear blocked status
$sth = $dbh->do("UPDATE User_list set blocked=0 where blocked=1");

# check quotes
foreach my $row (@$authlist_ref){
my ($a_login,$a_hour,$a_day,$a_month,$a_email,$a_blocked, $a_id) = @$row;
$blocked = 0; 
$userid = $a_id;

if ($a_hour ne "0")  {
    foreach my $rh (@$hour_q) {
    my ($f_hour, $f_id) = @$rh;
    if ($f_id eq $userid) {
	    $blocked = ($f_hour>$a_hour);
	    if (($f_hour>$a_hour) and ($a_blocked ne "1")){
		$mailbody = $mailbody . "$workdate - User $a_login is blocked! Hour quota! Hour traffic: $f_hour limit: $a_hour \n";
		}
	last;
	}
    }
}

if ($a_day ne "0") {
    foreach my $rd (@$day_q) {
    my ($f_day, $f_id) = @$rd;
    if ($f_id eq $userid) {
            $blocked+=($f_day>$a_day);
            if (($f_day>$a_day) and ($a_blocked ne "1")) {
		$mailbody = $mailbody . "$workdate - User $a_login is blocked! Daily quota! Day traffic: $f_day limit: $a_day\n";
                }
            last;
            }
    }
}
                                                                                        

if ($a_month ne "0") {
    foreach my $rm (@$month_q) {
    my ($f_month, $f_id) = @$rm;
    if ($f_id eq $userid) {
            $blocked+=($f_month>$a_month);
            if (($f_month>$a_month) and ($a_blocked ne "1")) {
		$mailbody = $mailbody . "$workdate - User $a_login is blocked! Month quota! Month traffic: $f_month limit: $a_month \n";
                }
            last;
            }
    }
}

if ($blocked ne "0") {
    $sth = $dbh->do("UPDATE User_list set blocked=1 where User_list.id=$a_id");
    };
    
}

$dbh->disconnect;

if ($mailbody ne "") {
print "$mailbody";
sendEmail("stat","dmitron\@sovtest\.ru","User blocked!",$mailbody);
}

exit 0;
