<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");
	
	if ($_POST["create"]) {
		header("location: addgroup.php");
	}
	if ($_POST["remove"]) {
           $fid = $_POST["fid"];
           while (list ($key,$val) = @each ($fid)) {
           if ($val) {
	    mysql_query("update User_list set Group_id=0 where Group_id=".$val);
	    mysql_query("delete from Group_filters where GroupId=".$val);
	    mysql_query("delete from Group_list where id=".$val);
	    }
	    }
	}
	
	unset($_POST); 
	require_once("../../inc/header.php");
?>
	<div id="cont">
	<table cellspacing="0" cellpadding="0"><tr><td>
        <b><a href=index.php>Список фильтров </a> | Группы фильтров<b><br></tr></table><br>
	<b>Список групп<br><br>
	<form name="def" action="groups.php" method="post"> 
	<table class="data" width="350" cellspacing="1" cellpadding="4">
	<tr align="center">
                <td ><input type="checkbox" onClick="checkAll(this.checked);"></a></td>
		<td ><b>Id</b></td>
		<td ><b>Название</b></td>
	</tr>
<?
	$users=mysql_query("select * from Group_list order by GroupName");
	while (list($id,$grpname)=mysql_fetch_array($users)) {
		print "<tr align=center>\n";
		print "<td class=\"data\" style='padding:0'><input type=checkbox name=fid[] value=$id></td>\n";
 		print "<td class=\"data\" ><input type=\"hidden\" name=\"id\" value=$id>$id</td>\n";
		print "<td class=\"data\"><a href=editgroup.php?id=$id>".$grpname."</a></td>\n";
	}
?>
	</table>
	<table cellspacing="0" cellpadding="0" width="350">
	<tr><td align="left"><input type="submit" name="remove" value="Удалить"> </td>
	<td><a href=addgroup.php>Создать группу</a></td>
	</tr>
	</table>
	</form>
<?	
	require_once("../../inc/footer.php");
?>