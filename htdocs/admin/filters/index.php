<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");
	
	if (isset($_POST["create"])) {
		$fname=$_POST["newfilter"];
		if (isset($_POST['filter_type'])) { $ftype=$_POST["filter_type"]*1; } else { $ftype=0;}
		if ($fname) {
		mysql_query("INSERT INTO filter_list (name,type) VALUES('$fname','$ftype')");
		list($id)=mysql_fetch_array(mysql_query("Select id from filter_list where name='$fname' and type='$ftype' order by id DESC"));
		header("location: editfilter.php?id=$id");
		}
	}

	if (isset($_POST["remove"])) {
           $fid = $_POST["fid"];
           while (list ($key,$val) = @each ($fid)) {
           if ($val) {
	    mysql_query("delete from User_filters where FilterId=$val");
	    mysql_query("delete from Group_filters where FiltrId=$val");
	    mysql_query("delete from filter_list where id=$val");
	    }
	    }
	}
	unset($_POST); 	
	require_once("../../inc/header.php");
?>
	<div id="cont">
	<table cellspacing="0" cellpadding="0">
	<tr><td>
	<b>Список фильтров | <a href=groups.php> Группы фильтров</a><b><br></tr></table>
	<form name="def" action="index.php" method="post"> 
	<table class="data" width="650" cellspacing="1" cellpadding="4">
	<tr align="center">
                <td><input type="checkbox" onClick="checkAll(this.checked);"></a></td>
		<td><b>Код</b></td>
		<td><b>Имя</b></td>
		<td><b>Тип</b></td>
		<td><b>Протокол</b></td>
		<td><b>Адрес назначения</b></td>
		<td><b>Порт</b></td>
		<td><b>Действие</b></td>
	</tr>
<?
	$filters=mysql_query("SELECT U.id, U.type, U.Name, U.proto, U.dst, U.dstport, U.action from filter_list U");
	while (list($id,$ftype,$fname,$fproto,$fdst,$fdstport,$faction)=mysql_fetch_array($filters)) {
		print "<tr align=center>\n";
		print "<td class=\"data\" style='padding:0'><input type=checkbox name=fid[] value=$id></td>\n";
 		print "<td class=\"data\" ><input type=hidden name=\"id\" value=$id>$id</td>\n";
		print "<td class=\"data\" align=left><a href=editfilter.php?id=$id>".$fname."</a></td>\n";
		if ($ftype==0) { print "<td class=\"data\">IP фильтр</td>\n";
		    print "<td class=\"data\">$fproto</td>\n";
    		    print "<td class=\"data\">$fdst</td>\n";
    		    print "<td class=\"data\">$fdstport</td>\n";
    		    print "<td class=\"data\">$faction</td>\n<tr>";
		    }
		    else  { print "<td class=\"data\">Name фильтр</td>\n";
		    print "<td class=\"data\"></td>\n";
    		    print "<td class=\"data\">$fdst</td>\n";
    		    print "<td class=\"data\"></td>\n";
    		    print "<td class=\"data\">$faction</td>\n<tr>";
		    }
	}
?>
	</table>
	<table class="data" cellspacing="0" cellpadding="0" width="650">
	<tr align=left>
	<td >Название <input type=text name=newfilter value="Unknown"></td>
	<td >Тип фильтра <select name="filter_type">
        <option value=0 selected>IP фильтр</option>
        <option value=1>Name фильтр</option>
        </select>
        </td>
        <td ><input type="submit" name="create" value="Добавить" ></td>
	<td align="right"><input type="submit" name="remove" value="Удалить"></td>
	</tr>
	</table>
	</form>
<?	require_once("../../inc/footer.php"); ?>
