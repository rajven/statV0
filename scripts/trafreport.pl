#!/usr/bin/perl  
#
# ARGV[0] - date
# ARGV[1] - groupid
# ARGV[2] - period

use DBI;
use Time::Local;
use Switch;

require "/home/stat/config.pl";

# report for group
$GroupId = $ARGV[1];
# report date
$ReportDate = $ARGV[0];
# month | day | year
$ReportType = $ARGV[2];

if ($GroupId eq "0") {$GroupId = "";}

if ($ReportDate eq "") { $ReportDate="now"; }

switch ($ReportDate) {
case "now" {
	$ReportDate = time; 
	($mday,$mon,$year) = (localtime($ReportDate))[3,4,5]; 
	$mon += 1; 
	$year += 1900; 
	$dat = "$year\.$mon\.$mday";
	}
case "yesterday" {
	$ReportDate = time - 86400;
	($mday,$mon,$year) = (localtime($ReportDate))[3,4,5]; 
	$mon += 1; 
	$year += 1900; 
	$dat = "$year\.$mon\.$mday";
	}
else 	{
	$ReportDate =~ s/\./-/g;
	($year,$mon,$mday) = split("-",$ReportDate);
	}
}

$GroupFlt = "";

if ($GroupId ne "") {$GroupFlt = " and (User_list.OU_id=$GroupId)";}

###
# Create new database handle. If we can't connect, die()
$dbh = DBI->connect("dbi:mysql:database=$DBNAME;host=$DBHOST","$DBUSER","$DBPASS");
if ( !defined $dbh ) { die "Cannot connect to mySQL server: $DBI::errstr\n";}


#User statictics

$sSQLD = "Select Login, OU.Name, OU.id,
SUM(tin)/1048576 as win, SUM(tout)/1048576 as wout, SUM(tproxy)/1048576 as wpr, (SUM(tin)+SUM(tproxy))/1048576 as wit 
FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout, SUM(proxytraf) as tproxy from User_traffic 
where ((YEAR(`Dat`)=$year) and (MONTH(`Dat`)=$mon) and (DAY(`Dat`)=$mday))
GROUP by userid) As V, User_auth, User_list, OU
WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.OU_id=OU.id) $GroupFlt
GROUP by Login 
Order by User_list.OU_id,Login";

$sSQLM = "Select Login, OU.Name, OU.id,
SUM(tin)/1048576 as win, SUM(tout)/1048576 as wout, SUM(tproxy)/1048576 as wpr, (SUM(tin)+SUM(tproxy))/1048576 as wit 
FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout, SUM(proxytraf) as tproxy from User_traffic 
where ((YEAR(`Dat`)=$year) and (MONTH(`Dat`)=$mon))
GROUP by userid) As V, User_auth, User_list, OU
WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.OU_id=OU.id) $GroupFlt
GROUP by Login 
Order by User_list.OU_id,Login";

$sSQLY = "Select Login, OU.Name, OU.id, 
SUM(tin)/1048576 as win, SUM(tout)/1048576 as wout, SUM(tproxy)/1048576 as wpr, (SUM(tin)+SUM(tproxy))/1048576 as wit 
FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout, SUM(proxytraf) as tproxy from User_traffic 
where ((YEAR(`Dat`)=$year))
GROUP by userid) As V, User_auth, User_list, OU
WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.OU_id=OU.id) $GroupFlt
GROUP by Login 
Order by User_list.OU_id,Login";

$sSQL = $sSQLD;

$ReportTitle = "Traffic report for $dat\n\n";

switch ($ReportType) {
case "month" { $sSQL = $sSQLM; $ReportTitle = "Traffic report for $year.$mon\n\n";}
case "year" {$sSQL = $sSQLY; $ReportTitle = "Traffic report for $year y.\n\n";}
}

printf "$ReportTitle";

$sth=$dbh->prepare($sSQL);

$s_in=0;$s_out=0;$s_p=0;$s_i=0;

$CurOu = "";
$Curgrp = 0;

printf "\nUser statistics, Mbytes \n";
printf "|----------------------------------------------------------------------------|\n";
printf "|    Login       |     in       |      out     |     proxy    |     Itog     | \n";
$sth->execute;

$Ok = 1;

while ($Ok) {

$Ok = ($login,$group,$grpid,$fb_in,$fb_out,$fproxy,$f_itog)=($sth->fetchrow());

$s_in +=$fb_in; $s_out += $fb_out; $s_p += $fproxy; $s_i +=$f_itog;
if (( $CurOu ne $group ) or not($Ok)) { 
	
	if ($Curgrp ne 0 ) {
	$sSQLD1 = "Select SUM(tin)/1048576 as win, SUM(tout)/1048576 as wout, SUM(tproxy)/1048576 as wpr, (SUM(tin)+SUM(tproxy))/1048576 as wit 
	FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout, SUM(proxytraf) as tproxy from User_traffic 
	where ((YEAR(`Dat`)=$year) and (MONTH(`Dat`)=$mon) and (DAY(`Dat`)=$mday))
	GROUP by userid) As V, User_auth, User_list
	WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.OU_id=$Curgrp)";

	$sSQLM1 = "Select SUM(tin)/1048576 as win, SUM(tout)/1048576 as wout, SUM(tproxy)/1048576 as wpr, (SUM(tin)+SUM(tproxy))/1048576 as wit 
	FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout, SUM(proxytraf) as tproxy from User_traffic 
	where ((YEAR(`Dat`)=$year) and (MONTH(`Dat`)=$mon))
	GROUP by userid) As V, User_auth, User_list
	WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.OU_id=$Curgrp)";

	$sSQLY1 = "Select SUM(tin)/1048576 as win, SUM(tout)/1048576 as wout, SUM(tproxy)/1048576 as wpr, (SUM(tin)+SUM(tproxy))/1048576 as wit 
	FROM (select userid, SUM(bytein) as tin, SUM(byteout) as tout, SUM(proxytraf) as tproxy from User_traffic 
	where ((YEAR(`Dat`)=$year))
	GROUP by userid) As V, User_auth, User_list
	WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.OU_id=$Curgrp)";

	$sSQL1 = $sSQLD1;
	switch ($ReportType) {
	case "month" { $sSQL1 = $sSQLM1;}
	case "year" {$sSQL1 = $sSQLY1; }
	}

	$fth=$dbh->prepare($sSQL1);
	$fth->execute;
	($ff_in,$ff_out,$ffproxy,$ff_itog)=($fth->fetchrow());
	$fth->finish;
	printf "|----------------------------------------------------------------------------|\n";
	printf "|                | %12u | %12u | %12u | %12u |\n",$ff_in,$ff_out,$ffproxy,$ff_itog;
	}
	printf "|----------------------------------------------------------------------------|\n";
	$CurOu = $group;
	printf "| %-74s |\n",$group;
	printf "|----------------------------------------------------------------------------|\n";
	$Curgrp = $grpid;
	}
	
if ($Ok) {printf "|%-15s | %12u | %12u | %12u | %12u |\n",$login,$fb_in,$fb_out,$fproxy,$f_itog;}

}
printf "| SUM            | %12u | %12u | %12u | %12u |\n\n",$s_in,$s_out,$s_p,$s_i;

if ($GroupFlt eq "") {

$wSQLD = "SELECT
DATE_FORMAT(`date`,'%Y-%m-%d') as dat, 
FORMAT(SUM(`wan_in`)/1048576,0) as wanin,
FORMAT(SUM(`wan_out`)/1048576,0) as wanout, 
FORMAT(SUM(`eth_wan`)/1048576,0) as ethwan,
FORMAT(SUM(`wan_eth`)/1048576,0) as waneth
FROM wan_traffic 
where (YEAR(Date)=$year and MONTH(Date)=$mon and DAY(Date)=$mday)
GROUP by DATE_FORMAT(`date`,'%Y-%m-%d')";

$wSQLM = "SELECT
DATE_FORMAT(`date`,'%Y-%m') as dat, FORMAT(SUM(`wan_in`)/1048576,0) as wanin,
FORMAT(SUM(`wan_out`)/1048576,0) as wanout, FORMAT(SUM(`eth_wan`)/1048576,0) as ethwan,
FORMAT(SUM(`wan_eth`)/1048576,0) as waneth
FROM wan_traffic 
where (YEAR(Date)=$year and MONTH(Date)=$mon)
GROUP by DATE_FORMAT(`date`,'%Y-%m')";

$wSQLY = "SELECT
DATE_FORMAT(`date`,'%Y') as dat, FORMAT(SUM(`wan_in`)/1048576,0) as wanin,
FORMAT(SUM(`wan_out`)/1048576,0) as wanout, 
FORMAT(SUM(`eth_wan`)/1048576,0) as ethwan,
FORMAT(SUM(`wan_eth`)/1048576,0) as waneth
FROM wan_traffic 
where (YEAR(Date)=$year)
GROUP by DATE_FORMAT(`date`,'%Y')";

$sSQL = $wSQLD;

switch ($ReportType) {
case "month" { $sSQL = $wSQLM; }
case "year" {$sSQL = $wSQLY; }
}

$sth = $dbh->prepare($sSQL);

if ( !defined $sth ) { die "Cannot prepare statement: $DBI::errstr\n";	}

# Execute the statement at the database level
$sth->execute;

if (($dt,$wan_in,$wan_out,$eth_wan, $wan_eth)=($sth->fetchrow()))

{
printf "Interface statistics in Mbytes\n\n";
printf "wan in: $wan_in \n";
printf "wan out: $wan_out \n";
printf "wan <-> eth: \n";
printf "	FORWARD  IN: $wan_eth \n";
printf "	FORWARD OUT: $eth_wan \n";
}
}

$sth->finish;

$dbh->disconnect;

exit 0;
