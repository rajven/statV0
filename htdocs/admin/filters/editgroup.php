<?php
	require_once("../../inc/auth.php");

	define("SQL",1);
	require_once("../../inc/sql.php");

	require_once("../../inc/languages/".$language.".php");

	$id=$_GET["id"]*1;
	
	if (!$_POST["id"] && !$id) {
		header("location: index.php");
	}
	
	if ($_POST["editgroup"]) {
		$id=$_POST["id"]*1;
		$grpname=mysql_escape_string(substr($_POST["groupname"],0,30));
		mysql_query("update Group_list set GroupName='$grpname' where id='$id'");
		header("location: index.php");
		}
	
	if ($_POST["addfilter"]) {
		$id=$_POST["id"]*1;
		$fid=$_POST["newfilter"]*1;
		list($forder)=mysql_fetch_array(mysql_query("SELECT MAX(GF.Order) FROM Group_filters GF where GroupId='$id'"));
		$forder++;
		mysql_query("insert into Group_filters (GroupId,FiltrId,Group_filters.Order) values('$id','$fid','$forder')");
 	}	
	

        if ($_POST["removefilter"]) {
		   $fgid=$_POST["fgid"];
                   while (list ($key,$val) = @each ($fgid)) {
	                 if ($val) {
			             mysql_query("delete from Group_filters where id=".$val*1);
		                 }
	             }
	}		     
        if (isset($_POST["saveorder"])) {
    		if ((isset($_POST["fgid"])) and (isset($_POST["ford"]))) {
		   $fgid=$_POST["fgid"];
		   $ford=$_POST["ford"];
                   while (list ($key,$val) = @each ($ford)) {
	                	$gid=$fgid[$key];
		    		mysql_query("Update Group_filters set Group_filters.Order=".$val." where Id=".$gid);
	            }
	        }
	}		     

	unset($_POST); 
	list($grpname)=mysql_fetch_array(mysql_query("select GroupName from Group_list where Id=$id"));
	
	require_once("../../inc/header.php");
?>
	<div id="cont">
	
	<form name="def" action="editgroup.php?id=<? echo $id; ?>" method="post">
	<input type="hidden" name="id" value=<? echo $id; ?>>
        <table class="data" cellspacing="0" cellpadding="4">
        <tr>
        <td>Название</td>
        <td><input type="text" name="groupname" value="<? echo $grpname; ?>" size=25></td>
        </tr>
	<tr>
	<td colspan=2>
	<input type="submit" name="editgroup" value="Сохранить">
	</td>
	</tr>
	</table>
	<br>
	<b>Список фильтров группы</b><br>
        <table class="data" cellspacing="1" cellpadding="4">
	<tr><td><input type="checkbox" onClick="checkAll(this.checked);"></a></td>
        <td>Order</td>
	<td>Название фильтра</td>
        <td align="right"><input type="submit" name="removefilter" value="Удалить Фильтр"></td>
	</tr>
<?
	$flist = mysql_query("SELECT G.id, G.FiltrId, f.Name, G.Order FROM Group_filters G, filter_list f WHERE f.id=G.Filtrid and  GroupId=$id Order by G.Order");
	while (list($fgid,$fid,$fname,$ford)=mysql_fetch_array($flist)) {
        print "<tr align=center>\n";
        print "<td class=\"data\" style='padding:0'><input type=checkbox name=fgid[] value=$fgid></td>\n";
        print "<td class=\"data\" align=left><input type=text name=ford[] value=$ford size=4 ></td>\n";
        print "<td class=\"data\" align=left><a href=editfilter.php?id=$fid>".$fname."</a></td>\n";
	print "<td class=\"data\"></td>\n";
	print "</tr>";
        }
?>
        </tr></table>
        <table cellspacing="0" cellpadding="0" width="550">
        <tr><td>
	<input type="submit" name="addfilter" value="Добавить фильтр" >	
	<select name="newfilter">
<?
	$filters = mysql_query("SELECT f.id,Name FROM filter_list f WHERE f.id not in (Select FiltrId from Group_filters where GroupId=$id)");
        while (list($fni,$fname)=mysql_fetch_array($filters)) {
	 print "<option value=$fni>$fname</option>";
	}
?>
	</select>
</td>
        <td align="right"><input type="submit" name="saveorder" value="Применить порядок"></td></tr>
	</table>
	</form>
<?
	require_once("../../inc/footer.php");
?>