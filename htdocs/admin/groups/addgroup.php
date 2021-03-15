<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");
	
	if ($_POST["addgroup"]) {
		$grpname=mysql_escape_string(substr($_POST["groupname"],0,30));
		mysql_query("insert into OU (name) values('$grpname')");
		header("location: index.php");
		}
	unset($_POST); 
	require_once("../../inc/header.php");
?>
	<div id="cont">
	<b>Создать группу</b>
	<form action="addgroup.php" method="post">
	<table class="data" cellspacing="0" cellpadding="4">
	<tr>
	<td>Название</td>
	<td><input type="text" name="groupname" size=25></td>
	</tr>
	<tr>
	<td colspan=2>
	<input type="submit" name="addgroup" value="Добавить">
	</td>
	</tr>
	</table>
	</form>
<?	
	require_once("../../inc/footer.php");
?>
