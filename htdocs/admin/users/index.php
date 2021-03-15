<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");
	
	$msg_error = "";
	
	if ($_POST["create"]) {
		$login=$_POST["newlogin"];
		if ($login) {
		list($lcount)=mysql_fetch_array(mysql_query("Select count(id) from User_list where LCase(Login)=LCase('$login')"));
		if ($lcount>0) {
		    $msg_error = "Логин $login в базе уже есть!";
		    unset($_POST);
		    }
		    else {
		    mysql_query("INSERT INTO User_list (Login,Group_id) VALUES('$login','2')");
		    list($id)=mysql_fetch_array(mysql_query("Select id from User_list where Login='$login' order by id DESC"));
		    header("location: edituser.php?id=$id");
		    }
		}
	}

	if ($_POST["remove"]) {
           $fid = $_POST["fid"];
           while (list ($key,$val) = @each ($fid)) {
           if ($val) {
#	    list($tcount)=mysql_fetch_array(mysql_query("Select count(T.userid) from User_traffic T where T.userid in (SELECT U.id FROM User_auth U, User_list U1 WHERE U1.id=U.userid and U1.id=$val)"));
#	    if ($tcount==0) {
#	    while (list($fauth)=mysql_fetch_array(mysql_query("Select id from User_auth where userid=$val")))
#	    {
#	    mysql_query("delete from User_filters where userid=".$fauth);
#	    }
	    mysql_query("delete from User_auth where userid=$val");
	    mysql_query("delete from User_list where id=$val");
#	    }
#	    else {
#	    mysql_query("Update User_auth set deleted=1 where userid=$val");
#	    mysql_query("Update User_list set deleted=1 where id=$val");
#	    }
	    }
	    }
	}

	if ($_POST["recheck"]) {
	    if ($_POST["recheck"]<>"") { exec("sudo -u root /home/stat/recheck.sh"); }
	    $_POST["recheck"]="";
	}

	if (isset($_POST['ou'])) { $rou=$_POST["ou"]*1; } else { $rou=0;}

	unset($_POST); 
	
	require_once("../../inc/header.php");
	
?>
      <div id="cont">
<?    if ($msg_error) { print "<div id='msg'><b>$msg_error</b></div><br>\n"; } ?>
  
      <form name="def" action="index.php" method="POST"> 
      <b>Список пользователей. Организация - </b>
      <select name="ou">
<?
      $ou=mysql_query("SELECT * FROM OU Order by name DESC");
      print "<option value=0 selected>All</option>";
      while (list($fid,$fname)=mysql_fetch_array($ou)) {
      if ($fid==($rou*1)) { print "<option value=$fid selected>$fname</option>";}
         else { print "<option value=$fid>$fname</option>"; }
       }
?>
     </select>
     <input type="submit" value="OK">
     
     <input type=submit name="recheck" value="Перестроить фильтры" >
     <table class="data" width="800" cellspacing="1" cellpadding="4">
	<tr align="center">
                <td><input type="checkbox" onClick="checkAll(this.checked);"></a></td>
		<td><b>Код</b></td>
		<td><b>Отчёт</b></td>
		<td><b>Организация</b></td>
		<td><b>Логин</b></td>
		<td><b>ФИО</b></td>
		<td><b>Фильтр</b></td>
		<td><b>Активен</b></td>
		<td><b>НАТ</b></td>
		<td><b>Прокси</b></td>
		<td><b>Заблокирован</b></td>
	</tr>
<?

	if ($rou == 0) { $sSQL = "SELECT U.id, O.name, U.Login, U.FIO, U.Enabled, U.nat, U.proxy, U.blocked, G.GroupName
                FROM User_list U, OU O, Group_list G where U.OU_id=O.id and U.Group_id=G.id and U.deleted=0 Order by Login"; }
		else   { $sSQL = "SELECT U.id, O.name, U.Login, U.FIO, U.Enabled, U.nat, U.proxy, U.blocked, G.GroupName
                FROM User_list U, OU O, Group_list G where U.OU_id=O.id and U.Group_id=G.id and U.deleted=0 and U.OU_id=$rou Order by Login";
		}

	$users=mysql_query($sSQL);
	
	while (list($id,$org,$login,$fio,$ena,$nat,$proxy,$block,$fltname)=mysql_fetch_array($users)) {
		$cl="data";
		if ($block) { $cl="info"; } 
		if (!$ena) { $cl="warn"; }
		print "<tr align=center>\n";
		print "<td class=\"$cl\" style='padding:0'><input type=checkbox name=fid[] value=$id></td>\n";
 		print "<td class=\"$cl\" ><input type=hidden name=\"id\" value=$id>$id</td>\n";
		print "<td class=\"$cl\" align=left><a href=../reports/userinfo.php?id=$id>Просмотр</a></td>\n";
		print "<td class=\"$cl\">$org</td>\n";
		print "<td class=\"$cl\" align=left><a href=edituser.php?id=$id>".$login."</a></td>\n";
		print "<td class=\"$cl\">$fio</td>\n";
		print "<td class=\"$cl\">$fltname</td>\n";
		print "<td class=\"$cl\">$ena</td>\n";
		print "<td class=\"$cl\">$nat</td>\n";
		print "<td class=\"$cl\">$proxy</td>\n";
		print "<td class=\"$cl\">$block</td>\n</tr>";
	}
?>
	</table>
	<table  class="data" cellspacing="1" cellpadding="4" width="350">
	<tr><td><input type=text name=newlogin value="Unknown" ></td><td><input type="submit" name="create" value="Добавить логин" ></td>
	<td align="right"><input type="submit" name="remove" value="Удалить"></td>
	</tr>
	</table>
	</form>
<?	
	require_once("../../inc/footer.php");
?>