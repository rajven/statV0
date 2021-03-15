#!/usr/bin/perl

use DBI;
use Time::Local;
use FileHandle;
use Switch;
use Data::Dumper;
require "/home/stat/config.pl";
use IPTables::libiptc; 

$dbh = DBI->connect("dbi:mysql:database=$DBNAME;host=$DBHOST","$DBUSER","$DBPASS");
if ( !defined $dbh ) {
  die "Cannot connect to mySQL server: $DBI::errstr\n";
  }

my @ipt_array = ();
my @ipt_nat_array = ();

push @ipt_nat_array,("-F _BLOCK");

# show message "Internet blocked!"
# get blocked user list
$sth = $dbh->prepare("SELECT User_auth.IP FROM User_auth,User_list
  WHERE User_list.id=User_auth.userid and User_auth.enabled=1 and 
  User_list.enabled=1 and User_list.blocked=1 and User_auth.deleted=0
  Order by User_auth.IP");
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;

my $fltlist_ref = $sth->fetchall_arrayref();
foreach my $row (@$fltlist_ref){
my ($a_ip) = @$row;
push @ipt_nat_array,("-A _BLOCK -s $a_ip -j _REDIRECT");
}

# get disabled user list
$sth = $dbh->prepare("SELECT User_auth.IP FROM User_auth,User_list
WHERE User_list.id=User_auth.userid and (User_auth.enabled=0 or User_list.enabled=0) and User_list.blocked=0 and User_auth.deleted=0
Order by User_auth.IP");


if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;
my $fltlist_ref = $sth->fetchall_arrayref();
foreach my $row (@$fltlist_ref){
my ($a_ip) = @$row;
push @ipt_nat_array,("-A _BLOCK -s $a_ip -j _REDIRECT");
}

### create new chain NEWUSERS
push @ipt_array,("-N NEWUSERS");
push @ipt_array,("-F NEWUSERS");

# get list blocked icq

#get userid list
$sth = $dbh->prepare( "
  SELECT User_auth.IP
  FROM User_auth, User_list
  WHERE User_list.id=User_auth.userid and User_auth.enabled=1 and User_list.blocked=0 and User_auth.deleted=0 and User_list.enabled=1 and User_list.icq=0
  Order by User_auth.IP");

if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }

$sth->execute;

# user auth list
my $fltlist_ref = $sth->fetchall_arrayref();

foreach my $row (@$fltlist_ref){
my ($a_ip) = @$row;
push @ipt_array,("-A NEWUSERS -s $a_ip -j DROPICQ");
push @ipt_array,("-A NEWUSERS -d $a_ip -j DROPICQ");
}

#get userid list
$sth = $dbh->prepare( "
  SELECT User_auth.IP,
  f.proto, f.dst, f.dstport, f.action
  FROM User_auth, User_list, Group_filters G, filter_list f 
  WHERE f.id=G.Filtrid and G.Groupid=User_list.Group_id and User_list.id=User_auth.userid and User_auth.enabled=1
  and User_auth.nat=1 and User_auth.grpflt=1 and User_list.blocked=0 and User_auth.deleted=0 and f.type=0
  Order by User_auth.IP,G.Order,f.action ");

if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }

$sth->execute;

# user auth list
my $fltlist_ref = $sth->fetchall_arrayref();

foreach my $row (@$fltlist_ref){
my ($a_ip,$a_proto,$a_dst,$a_dport,$a_action) = @$row;

$f_str = "";$f1_str="";
if (($a_proto ne "") and ($a_proto ne "all")) { $f_str = " -p $a_proto"; $f1_str = " -p $a_proto";}
if (($a_dst ne "") and ($a_dst ne "0/0")) { $f_str = $f_str." -d $a_dst"; $f1_str = $f1_str . " -s $a_dst"; }
if (($a_dport ne "") and ($a_dport ne "0")) { $f_str = $f_str." --dport $a_dport"; $f1_str = $f1_str . " --sport $a_dport";}

switch ($a_action){
case "0" { $f_str = $f_str." -j DROP";  $f1_str = $f1_str . " -j DROP";}
case "1" { $f_str = $f_str." -j ACCEPT"; $f1_str = $f1_str . " -j ACCEPT"; }
else { $f_str = $f_str." -j REJECT"; $f1_str = $f1_str . " -j REJECT"; }
}

push(@ipt_array,"-A NEWUSERS -s $a_ip $f_str");
push(@ipt_array,"-A NEWUSERS -d $a_ip $f1_str");
}

# user filters

$sth = $dbh->prepare( "SELECT User_auth.IP, f.proto, f.dst, f.dstport, f.action
  FROM User_auth, User_list, User_filters UF, filter_list f 
  WHERE UF.Userid=User_auth.id and User_list.enabled=true and f.id=UF.Filterid
  and User_list.blocked=0 and User_auth.deleted=0
  and User_list.id=User_auth.userid and f.type=0
  Order by UF.Order" );

if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }

$sth->execute;

# user auth list
my $fltlist_ref = $sth->fetchall_arrayref();

foreach my $row (@$fltlist_ref){
my ($a_ip,$a_proto,$a_dst,$a_dport,$a_action) = @$row;

$f_str = "";$f1_str="";
if (($a_proto ne "") and ($a_proto ne "all")) { $f_str = " -p $a_proto"; $f1_str = " -p $a_proto";}
if (($a_dst ne "") and ($a_dst ne "0/0")) { $f_str = $f_str." -d $a_dst"; $f1_str = $f1_str . " -s $a_dst"; }
if (($a_dport ne "") and ($a_dport ne "0")) { $f_str = $f_str." --dport $a_dport"; $f1_str = $f1_str . " --sport $a_dport";}

switch ($a_action){
case "0" { $f_str = $f_str." -j DROP";  $f1_str = $f1_str . " -j DROP";}
case "1" { $f_str = $f_str." -j ACCEPT"; $f1_str = $f1_str . " -j ACCEPT"; }
else { $f_str = $f_str." -j REJECT"; $f1_str = $f1_str . " -j REJECT"; }
}

push(@ipt_array,"-A NEWUSERS -s $a_ip $f_str");
push(@ipt_array,"-A NEWUSERS -d $a_ip $f1_str");
}

#get userid list
$sth = $dbh->prepare( "SELECT User_auth.IP,User_auth.mac  FROM User_auth WHERE User_auth.deleted=0 Order by User_auth.IP");
if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n"; }
$sth->execute;

# user auth list
my $fltlist_ref = $sth->fetchall_arrayref();
push @ipt_array,("-F _MACCHECK");
foreach my $row (@$fltlist_ref){
my ($a_ip,$a_mac) = @$row;
if ((defined $a_mac) and ($a_mac ne "")) { push @ipt_array,("-A _MACCHECK -s $a_ip -m mac --mac-source $a_mac -j RETURN"); }
    else { push @ipt_array,("-A _MACCHECK -s $a_ip -j RETURN"); }
}
push @ipt_array,("-A _MACCHECK -j REJECT");

my $nat_table = IPTables::libiptc::init('nat');
foreach my $row (@ipt_nat_array) {
my @cmd_array = split(" ",$row);
$nat_table->iptables_do_command(\@cmd_array);
}
$nat_table->commit();

my $table = IPTables::libiptc::init('filter');
foreach my $row (@ipt_array) {
my @cmd_array = split(" ",$row);
$table->iptables_do_command(\@cmd_array);
}

$table->commit();

system("/sbin/iptables -A NEWUSERS -j DROP");
### rename work chain USERS to TMP
system("/sbin/iptables -E USERS TMP");
### rename NEWUSERS to USERS
system("/sbin/iptables -E NEWUSERS USERS");
### retarget FORWARD
system("/sbin/iptables -D FORWARD -j TMP");
system("/sbin/iptables -A FORWARD -j USERS");
## delete TMP
system("/sbin/iptables -F TMP");
system("/sbin/iptables -X TMP");

exit 0;
