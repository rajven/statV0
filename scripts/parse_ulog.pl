#!/usr/bin/perl
use DBI;
use Time::Local;
use Net::Patricia;
#use Data::Dumper;
require "/home/stat/config.pl";

### globals

###
# Create new database handle. If we can't connect, die()
$dbh = DBI->connect("dbi:mysql:database=$DBNAME;host=$DBHOST","$DBUSER","$DBPASS");

if ( !defined $dbh ) {    die "Cannot connect to mySQL server: $DBI::errstr\n"; }

# clear conters
my $wan_in=0;
my $wan_out=0;
my $eth_wan=0;
my $wan_eth=0;

#get userid list
my $sth = $dbh->prepare( "SELECT id,IP FROM User_auth where (enabled=1 and deleted=0) ORDER by IP" );
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }

$sth->execute;

# net objects
my $t = new Net::Patricia;
my $f = new Net::Patricia;
my $u = new Net::Patricia;

# user auth list
my $authlist_ref = $sth->fetchall_arrayref();

foreach my $row (@$authlist_ref) {
#forward in
@$row[2] = 0;
#forward out
@$row[3] = 0;
#proxy
@$row[4] = 0;
$u->add_string($row->[1],$row);
}

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
$nets_ref = $sth->fetchall_arrayref();
foreach my $row (@$nets_ref){
$f->add_string($row->[0],$row);
}

my $first = 1;

my $time_string;
my $dbtime;
my $sdate;
my $ftime;

my $p_counter = 0;

my ($min,$hour,$mday,$mon,$year) = (localtime())[1,2,3,4,5];
$mon += 1;  $year += 1900;

while (<>) {

my ($l_tim,$l_proto,$l_src_ip,$l_src_port,$l_dst_ip,$l_dst_port,$l_packets,$l_bytes,$l_input,$l_output,$l_prefix) = split(" ");


$l_input =~ s/\"//g;
$l_output =~ s/\"//g;
$l_prefix =~ s/\"//g;

next if (($l_input eq "lo") or ($l_output eq "lo"));

$p_counter++;

# clear conters for user stats
my $traf_proxy=0; 
my $forward_out=0; 
my $forward_in=0;
my $UserIp=""; 
my $flog = 1; 
my $ffree= 0;

($min,$hour,$mday,$mon,$year) = (localtime($l_tim))[1,2,3,4,5];
    $mon += 1;  $year += 1900;
    $time_string = "$year-$mon-$mday $hour:$min:00";
    $dbtime = $dbh->quote($time_string);
    $sdate = $dbh->quote("$year-$mon-$mday $hour:00:00");
    $ftime = $dbh->quote("$year-$mon-$mday $hour:$min:00");
    $first=0;

# get user statistics
if ($l_prefix eq "FORWARD") {
    # to wan
    if (($l_input ne $wan_dev) and ($l_output eq $wan_dev)) {
	    # check for free nets
	    $ok = ($f->match_string($l_dst_ip));
	    if (!$ok)
        	{ $ok = ($t->match_string($l_dst_ip));
        	if ($ok) { $ffree = 1;  $flog = @$ok[1]; }
        	}
	    if (!$ffree) {
    		$forward_out = $l_bytes;
    		$UserIp = $l_src_ip;
    		}
	    }
    if (($l_output ne $wan_dev) and ($l_input eq $wan_dev)) {
	    # check for free nets
	    $ok = ($f->match_string($l_src_ip));
	    if (!$ok)
        	{ $ok = ($t->match_string($l_src_ip));
        	if ($ok) { $ffree = 1;  $flog = @$ok[1]; }
        	}
	    if (!$ffree) {
    		$forward_in = $l_bytes;
    		$UserIp = $l_dst_ip;
    		}
	    }

    if (($l_output eq $wan_dev) and (($l_input eq "tap0") or ($l_input =~/ppp/))) {
            $forward_out = $l_bytes;
            $UserIp = $l_src_ip;
            $flog = 1;
            $ffree=0;
            }
    if (($l_input eq $wan_dev) and (($l_output eq "tap0") or ($l_output =~/ppp/))) {
            $forward_in = $l_bytes;
            $UserIp = $l_dst_ip;
            $flog = 1;
            $ffree=0;
            }

    if (($l_output ne $wan_dev) and (($l_input eq "tap0") or ($l_input=~/ppp/))) {
            $forward_in = $l_bytes;
            $UserIp = $l_dst_ip;
            $flog = 1;
            $ffree=0;
            }
    if (($l_input ne $wan_dev) and (($l_output eq "tap0") or ($l_output=~/ppp/))) {
            $forward_out = $l_bytes;
            $UserIp = $l_src_ip;
            $flog = 1;
            $ffree=0;
            }
    }

if (($ffree) and not($flog)) { next; }

#check proxy traf

if (($l_prefix eq "OUTPUT") and ($l_output ne $wan_dev) and ($l_src_port eq "3128")) {
    $UserIp = $l_dst_ip;
    $traf_proxy = $l_bytes;
    $flog = 0;
    $ffree = 0;
   }

if (($l_output eq $wan_dev) or ($l_input eq $wan_dev)) {
    # input|output for wan
    if (($l_prefix eq "INPUT") and ($l_input eq $wan_dev)) {
        	    # check for free nets
		    $ok = ($f->match_string($l_src_ip));
    		    if (not($ok)) { 
			    $ok = ($t->match_string($l_src_ip));
		    	    if ($ok) { $ffree = 1;  $flog = @$ok[1]; }
    			    }
    		    if (not($ffree)) { $wan_in += $l_bytes; }
    		    }
    if (($l_prefix eq "OUTPUT") and ($l_output eq $wan_dev)) {
	    	    # check for free nets
    		    $ok = ($f->match_string($l_dst_ip));
	            if (not($ok)) { 
				$ok = ($t->match_string($l_dst_ip));
			        if ($ok) { $ffree = 1;  $flog = @$ok[1]; }
			    }
		    if (not($ffree)) { $wan_out += $l_bytes; }
		    }
    }

if (($flog) and ($l_prefix eq "FORWARD")) {
    $res = $dbh->do("INSERT INTO All_traf (dt,proto,srcip,srcport,dstip,dstport,bytes,prefix,free) VALUES($ftime,'$l_proto','$l_src_ip','$l_src_port','$l_dst_ip','$l_dst_port','$l_bytes','$l_prefix','$ffree')");
    }


if (not($ffree)) {
    if (($l_prefix eq "FORWARD") and (($l_input ne "tap0") or ($l_output ne "tap0")) and (!($l_input=~/ppp/) or !($l_output=~/ppp/))) {
        $wan_eth += $l_bytes if (($l_input eq $wan_dev) and ($l_output ne $wan_dev));
	$eth_wan += $l_bytes if (($l_input ne $wan_dev) and ($l_output eq $wan_dev));
	}
    }

# check for interfaces
if ($UserIp eq "") { next; };
if ($ffree) { next; };

# find user id
$ok = $u->match_string($UserIp);
if ($ok) { 
    @$ok[2] += $forward_in;
    @$ok[3] += $forward_out;
    @$ok[4] += $traf_proxy;
    }
}

######## user statistics

# update database
foreach my $row (@$authlist_ref) {
my ($a_id,$a_ip,$a_in,$a_out,$a_proxy) = @$row;
next if ($a_in+$a_out+$a_proxy eq "0");
# insert row
$res = $dbh->do("INSERT INTO User_traffic (Dat,userid,bytein,byteout,proxytraf) VALUES($dbtime,'$a_id','$a_in','$a_out','$a_proxy')");
unless ($res) {	die "Cannot update db\n"; }
}

### hour stats

# get current stats
my $sql = "Select id,userid, SUM(bytein),SUM(byteout) from User_stats WHERE ((YEAR(Dat)=$year) and (MONTH(Dat)=$mon) and (DAY(Dat)=$mday) and (HOUR(Dat)=$hour)) Group by userid order by userid";
my $fth = $dbh->prepare($sql);
$fth->execute;

my $hour_stats=$fth->fetchall_arrayref();

# update database
foreach my $row (@$authlist_ref) {
my ($a_id,$a_ip,$a_in,$a_out,$a_proxy) = @$row;
    my $sum_traf = 0;
    ### find current statistics
    foreach my $row2 (@$hour_stats) {
    my ($f_s,$f_id,$f_in,$f_out) = @$row2;
    if ($a_id eq $f_id) {
	$sum_traf = $f_in + $a_in + $a_proxy;  
	$f_out = $f_out + $a_out;
        $ssql="UPDATE User_stats set bytein='$sum_traf', byteout='$f_out' WHERE (id=$f_s)";
        $res = $dbh->do($ssql); 
	unless ($res) {	$dbh->do("INSERT INTO User_stats (Dat,userid,bytein,byteout) VALUES($sdate,'$a_id','$sum_traf','$f_out')"); }
	last;
        }
    }
    ### if statistics not exists - add new record    
    if ($sum_traf eq "0") {
	$sum_traf = $a_in + $a_proxy;  
        $ssql="INSERT INTO User_stats (Dat,userid,bytein,byteout) VALUES($sdate,'$a_id','$sum_traf','$a_out')";
        $res = $dbh->do($ssql);
        }
}


###### wan statistics
# kill old  db row for wan stat
$res = $dbh->do("DELETE FROM wan_traffic WHERE (date = $dbtime)");
unless ($res) { die "Cannot update db\n";}
    
# insert db row
$res = $dbh->do("INSERT INTO wan_traffic SET date = $dbtime, wan_in = $wan_in, wan_out = $wan_out, eth_wan = $eth_wan, wan_eth = $wan_eth");
unless ($res) {	die "Cannot update db\n"; }

$sth->finish;
$fth->finish;

$dbh->disconnect;

exit 0;
