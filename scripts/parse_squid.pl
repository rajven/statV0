#!/usr/bin/perl  
#

use DBI;
use Time::Local;
use Net::Patricia;
require "/home/stat/config.pl";

###
# Create new database handle. If we can't connect, die()
$dbh = DBI->connect("dbi:mysql:database=$DBNAME;host=$DBHOST","$DBUSER","$DBPASS");
if ( !defined $dbh ) {
    die "Cannot connect to mySQL server: $DBI::errstr\n";
}


# net objects
$t = new Net::Patricia;
$f = new Net::Patricia;
$u = new Net::Patricia;

#get userid list
$sth = $dbh->prepare( "SELECT User_auth.id,IP FROM User_auth,User_list where (User_auth.userid=User_list.id and User_auth.enabled=1 
    and  User_auth.deleted=0 and  User_auth.proxy=1) ORDER by IP" );
    
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n";	}

$sth->execute;
# user auth list
my $authlist_ref = $sth->fetchall_arrayref();

foreach my $row (@$authlist_ref){
@$row[2] = 0;
$u->add_string($row->[1],$row);
}

$N=0;

#get list free nets
$sth = $dbh->prepare( "SELECT expression,log FROM nets where domain=0 and free=1 ORDER by free ASC" );
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute();
# free nets list
my $nets_ref = $sth->fetchall_arrayref();
foreach my $row (@$nets_ref){
$t->add_string($row->[0],$row);
}
# not free nets
$sth = $dbh->prepare( "SELECT expression,log FROM nets where domain=0 and free=0 ORDER by free ASC" );
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute();
# nets list
my $nets_ref = $sth->fetchall_arrayref();
foreach my $row (@$nets_ref){
$f->add_string($row->[0],$row);
}

#get list domain nets
$sth = $dbh->prepare( "SELECT expression,free,log FROM nets where domain=1 ORDER by free ASC" );
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;
# nets list
my $nets_ref = $sth->fetchall_arrayref();

while (<>) {
($l_tim,$l1,$l_src,$l_miss,$l_bytes,$l6,$l_url,$l7,$l8,$l9,$l_mime) = split(" ");

$l_miss=~s/.*\///;
$l_server = $l_url; $l_server =~s/.*\/\///; $l_server = substr($l_server,0,index($l_server,"\/"));
if ($l_miss ge "400") { next; }

#if ($l_bytes<32000) { next; }

$ffree=0;
$flog=1;
# check for localnet by domain name
foreach my $row (@$nets_ref){
$ffree = ($l_server =~ /$row->[0]/);
if ($ffree)
    {
    if (@$row[1] eq "0") { $ffree = not($ffree); }
        else { $flog = @$row[2]; }
    last;
    }
}

# check for ip nets
eval {
$ok = $f->match_string($l_server);
 
if ($ok) { $ffree=0; }
    else {
         $ok = ($t->match_string($l_server));
         if ($ok) { $ffree = 1;  $flog = @$ok->[1]; }
         }
};

if ($ffree and not($flog)) { next; }


if ($N eq "0") {
    ### get time 
    ($min,$hour,$mday,$mon,$year) = (localtime($l_tim))[1,2,3,4,5];
    $mon += 1;
    $year += 1900;
    $ftime = $dbh->quote("$year-$mon-$mday $hour:$min:00");
    $dbtime=$dbh->quote("$year-$mon-$mday $hour:$min:00"); 
    $dat=$dbh->quote("$year-$mon-$mday");
    $tim=$dbh->quote("$hour:$min:00"); 
    $fday=$mday;$fhour=$hour;$fmin=$min;
    $N=1;
    $res = $dbh->do("DELETE FROM squid_log WHERE dt=$ftime");
    }

$l_url = $dbh->quote("$l_url");
$l_server = $dbh->quote("$l_server");
$l_mime = $dbh->quote("$l_mime");

# find user id
$ok = $u->match_string($l_src);
if ($ok) {
	$res = $dbh->do("INSERT INTO squid_log (dt,url,bytes,server,userid)  VALUES($ftime,$l_url,'$l_bytes',$l_server,'@$ok->[0]')");
	if (not($ffree)) { @$ok[2] += $l_bytes; }
	}
}

exit 0;
