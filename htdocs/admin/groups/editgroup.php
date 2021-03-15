<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");

	$id=$_GET["id"]*1;
	if (!$_POST["id"] && !$id) {
		header("location: index.php");
	}
	
	if ($_POST["editgroup"]) {
		$id=$_POST["id"]*1;
		$grpname=mysql_escape_string(substr($_POST["name"],0,30));
		mysql_query("update OU set name='$grpname' where id='$id'");
		header("location: index.php");
		}
	
	unset($_POST); 

	list($grpname)=mysql_fetch_array(mysql_query("select name from OU where Id=$id"));
	
	
	require_once("../../inc/header.php");
?>
	<div id="cont">

	<form name="def" action="editgroup.php?id=<? echo $id; ?>" method="post">
	<input type="hidden" name="id" value=<? echo $id; ?>>
        <table class="data" cellspacing="0" cellpadding="4">
        <tr>
        <td>Название</td>
        <td><input type="text" name="name" value="<? echo $grpname; ?>" size=25></td>
        </tr>
	<tr>
	<td colspan=2>
	<input type="submit" name="editgroup" value="Сохранить">
	</td>
	</tr>
	</table>
	</form>
<?
	require_once("../../inc/footer.php");
?>