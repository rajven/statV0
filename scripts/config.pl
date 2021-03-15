#!/usr/bin/perl

### DB properties
$DBHOST = "localhost";
$DBNAME = "rstat";
$DBUSER = "stat";
$DBPASS = "werwerw";

# options
$Admin_email = "root";
$wan_dev = "eth1";
$proxy_port = "3128";

# Paths
$HOMEDIR = "/home/stat";

sub sendEmail
{
my ($to, $from, $subject, $message) = @_;
my $sendmail = '/usr/lib/sendmail';
open(MAIL, "|$sendmail -oi -t");
print MAIL "From: $from\n";
print MAIL "To: $to\n";
print MAIL "Subject: $subject\n\n";
print MAIL "$message\n";
close(MAIL);
}

