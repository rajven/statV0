<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");

	$id=$_GET["id"]*1;
	if (!$_POST["id"] && !$id) {
		header("location: index.php");
	}
	
	if ($_POST["editfilter"]) {
		$id=$_POST["id"]*1;
		$fname=mysql_escape_string(substr($_POST["fname"],0,30));
		$fdst=mysql_escape_string(substr($_POST["fdst"],0,120));
		$fproto=mysql_escape_string(substr($_POST["fproto"],0,10));
		$fdstport=mysql_escape_string(substr($_POST["fdstport"],0,14));
		$faction=$_POST["faction"]*1;
		mysql_query("update filter_list set Name='$fname',dst='$fdst',proto='$fproto',dstport='$fdstport',action='$faction' where id='$id'");
		header("location: index.php");
		}
	
	unset($_POST); 

        $filters=mysql_query("SELECT U.Name, U.type, U.proto, U.dst, U.dstport, U.action from filter_list U where id=$id");
	list($fname,$ftype,$fproto,$fdst,$fdstport,$faction)=mysql_fetch_array($filters);
			
	require_once("../../inc/header.php");
	print "<div id=cont>";
	print "<form name=def action=editfilter.php?id=$id method=post>";
	print "<input type=hidden name=id value=$id>";

    if ($ftype==0) { 
	print "<table class=\"data\" cellspacing=\"0\" cellpadding=\"4\">";
        print "<tr><td ><b>Имя</b></td>";
        print "<td ><b>Протокол</b></td>";
        print "<td ><b>Адрес назначения</b></td>";
        print "<td ><b>Порт</b></td>";
        print "<td ><b>Действие</b></td>";
       	print "</tr><td align=left><input type=text name=fname value=$fname size=20></td>";
       	print "<td ><input type=text name=fproto value=\"$fproto\" size=5></td>";
       	print "<td ><input type=text name=fdst value=\"$fdst\" size=40></td>";
       	print "<td ><input type=text name=fdstport value=\"$fdstport\" size=20></td>";
       	print "<td ><input type=text name=faction value=\"$faction\" size=1></td><tr>";
       	print "<tr><td colspan=2><input type=submit name=editfilter value=Сохранить></td>";
       	print "</tr></table>";
       	}
	else {
	print "<table class=\"data\" cellspacing=\"0\" cellpadding=\"4\">";
        print "<tr><td ><b>Имя</b></td>";
        print "<td ><b>Адрес назначения</b></td>";
        print "<td ><b>Действие</b></td></tr>";
       	print "<td align=left><input type=text name=fname value=\"$fname\" size=20></td>";
       	print "<td ><input type=text name=fdst value=\"$fdst\" size=120></td>";
       	print "<td ><input type=text name=faction value=\"$faction\" size=1></td><tr>";
       	print "<tr><td colspan=2><input type=submit name=editfilter value=Сохранить></td>";
       	print "</tr></table>";
	}
	print "</form>";
	require_once("../../inc/footer.php");
?>