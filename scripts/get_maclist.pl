#!/usr/bin/perl
use DBI;
require "/home/stat/config.pl";

my $dns_list = $ARGV[0];

if (!$dns_list) { exit 10; }

###
# Create new database handle. If we can't connect, die()
$dbh = DBI->connect("dbi:mysql:database=$DBNAME;host=$DBHOST","$DBUSER", "$DBPASS");

if ( !defined $dbh ) {    die "Cannot connect to mySQL server: $DBI::errstr\n"; }

open (D,">$dns_list") || die("Error open $dns_list for write!!! Die...");

$sth = $dbh->prepare( "SELECT IP,mac FROM User_auth where deleted=0 ORDER by IP" );
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;
# user auth list
my $authlist_ref = $sth->fetchall_arrayref();
foreach my $row (@$authlist_ref){
if ((defined $row->[1]) and ($row->[1] ne ""))  { print D "dhcp-host=$row->[1],$row->[0]\n"; }
}
close(D);

exit 0;
