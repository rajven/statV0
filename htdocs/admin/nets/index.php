<?php
	require_once("../../inc/auth.php");
	
	require_once("../../inc/languages/".$language.".php");
	
	if ($_POST["create"]) {
		$fname=$_POST["newnets"];
		if ($fname) {
		mysql_query("INSERT INTO nets (name) VALUES('$fname')");
		list($id)=mysql_fetch_array(mysql_query("Select id from nets where name='$fname' order by id DESC"));
		header("location: editnet.php?id=$id");
		}
	}

	if ($_POST["remove"]) {
           $fid = $_POST["fid"];
           while (list ($key,$val) = @each ($fid)) {
           if ($val) {
	    mysql_query("delete from nets where id=$val");
	    }
	    }
	}
	unset($_POST); 
	
	require_once("../../inc/header.php");
?>
	<div id="cont">
	<table cellspacing="0" cellpadding="0">
	<tr><td>
	<b>Список бесплатных сетей</b></tr></table>
	<form name="def" action="index.php" method="post"> 
	<table class="data" width="650" cellspacing="1" cellpadding="4">
	<tr align="center">
                <td><input type="checkbox" onClick="checkAll(this.checked);"></a></td>
		<td><b>id</b></td>
		<td><b>Название</b></td>
		<td><b>Сеть</b></td>
		<td><b>Бесплатно</b></td>
		<td><b>Лог</b></td>
		<td><b>Домен</b></td>
	</tr>
<?
	$filters=mysql_query("SELECT U.id, U.Name, U.expression, U.free, U.log, U.domain from nets U");
	while (list($id,$fname,$fexpr,$ffree,$flog,$fdomain)=mysql_fetch_array($filters)) {
		print "<tr align=center>\n";
		print "<td class=\"data\" style='padding:0'><input type=checkbox name=fid[] value=$id></td>\n";
 		print "<td class=\"data\" ><input type=hidden name=\"id\" value=$id>$id</td>\n";
		print "<td class=\"data\" align=left><a href=editnet.php?id=$id>".$fname."</a></td>\n";
		print "<td class=\"data\">$fexpr</td>\n";
		print "<td class=\"data\">$ffree</td>\n";
		print "<td class=\"data\">$flog</td>\n";
		print "<td class=\"data\">$fdomain</td>\n<tr>";
	}
?>
	</table>
	<table cellspacing="0" cellpadding="0" width="350">
	<tr>
	<td><input type="submit" name="create" value="Добавить сеть" ></td>
	<td><input type=text name=newnets value="Unknown" ></td>
	<td align="right"><input type="submit" name="remove" value="Удалить"></td>
	</tr>
	</table>
	</form>
<?	require_once("../../inc/footer.php"); ?>
