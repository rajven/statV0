#!/usr/bin/perl                                                                                                                                              
#                                                                                                                                                            
# ARGV[0] - TYPE (add,del,old)                                                                                                                                            
# ARGV[1] - MAC                                                                                                                               
# ARGV[2] - IP
# ARGV[3] - name
                                                                                                                                                             
use DBI;                                                                                                                                                     
use Time::Local;                                                                                                                                             
use Net::Patricia;
                                                                                                                                                             
require "/home/stat/config.pl";                                                                                                                              
                                                                                                                                                             
$A_TYPE = $ARGV[0];
$A_MAC = $ARGV[1];
$A_IP = $ARGV[2];
$A_NAME = $ARGV[3];

###
# Create new database handle. If we can't connect, die()
$dbh = DBI->connect("dbi:mysql:database=$DBNAME;host=$DBHOST","$DBUSER", "$DBPASS");

if ( !defined $dbh ) {    die "Cannot connect to mySQL server: $DBI::errstr\n"; }

#get userid list
$sth = $dbh->prepare( "SELECT id,IP,mac FROM User_auth where (enabled=1 and deleted=0) ORDER by IP" );
if ( !defined $sth ) {
        die "Cannot prepare statement: $DBI::errstr\n";
}

$sth->execute;

# net objects
$u = new Net::Patricia;

# user auth list
my $authlist_ref = $sth->fetchall_arrayref();

foreach my $row (@$authlist_ref){
$u->add_string($row->[1],$row);
}

# find auth by ip
# if found - > update mac 
# else find user by name
#	if found - >insert new auth with ip,mac
#	else add new user by name and add new auth ip,mac
#	update dnsmasq conf file
open (D,">/etc/dnsmasq.d/userlist") || die("Error open /etc/dnsmasq.d/userlist for write!!! Die...");
$sth = $dbh->prepare( "SELECT IP,mac FROM User_auth where (enabled=1 and deleted=0) ORDER by IP" );
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;
# user auth list
my $authlist_ref = $sth->fetchall_arrayref();
foreach my $row (@$authlist_ref){
if ((defined $row->[1]) and ($row->[1] ne ""))  { print D "dhcp-host=$row->[1],$row->[0]\n"; }
}
close(D);   
`service dnsmasq restart >/dev/null 2>&1`;
#	update iptables
exit 0;
