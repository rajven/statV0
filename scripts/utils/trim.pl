#!/usr/bin/perl

$i = 0;
while (<>) {
$line=$_;
if ($i ne 0) {
$line=~ s/\s//;
print "$line";
}
else
{ $i=1; }
}

exit 0;
