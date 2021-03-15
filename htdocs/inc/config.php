<?php
	if (!defined("CONFIG"))die("Not defined");

	
        /* MySQL settings */
        $dbhost="localhost";
        $dbname="rstat";
        $dbuser="stat";
        $dbpass="wertrwet";
	
	$language="russian";
	$style="white";
#	$style="dark"; #css ctyle
	$KB=1024;

function fbytes($traff) {
	$KB=1024;
	$units = array("", "k", "M", "G", "T");
	if ($traff) {
		$index = min(((int)log($traff,$KB)), count($units)-1);
                $result = round($traff/pow($KB,$index), 3).' '.$units[$index].'b';
	        } 
		else { $result = '0 b'; }
        return $result;
}

function IsMacValid($mac)
{
  return (preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $mac) == 1);
}

function checkValidIp($cidr) {

// Checks for a valid IP address or optionally a cidr notation range
// e.g. 1.2.3.4 or 1.2.3.0/24
	
if(!eregi("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(/[0-9]{1,2}){0,1}$", $cidr)) {
 $return = FALSE;
 } else {
 $return = TRUE;
 }
			           
if ( $return == TRUE ) {
   $parts = explode("/", $cidr);
   $ip = $parts[0];
   $netmask = $parts[1];
   $octets = explode(".", $ip);
   foreach ( $octets AS $octet ) {
   if ( $octet > 255 ) {
       $return = FALSE;
       }
   }
if ( ( $netmask != "" ) && ( $netmask > 32 ) ) {
   $return = FALSE;
   }
}
return $return;
}

?>