<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");

	$id=$_GET["id"]*1;
	if (!$_POST["id"] && !$id) {
		header("location: index.php");
	}
	
	$msg_error = "";
	
	if ($_POST["edituser"]) {
		$id=$_POST["id"]*1;
		$login=mysql_escape_string(substr($_POST["login"],0,30));
		$fio=mysql_escape_string(substr($_POST["fio"],0,30));
		$email=mysql_escape_string(substr($_POST["email"],0,30));
		$nat=$_POST["nat"]*1;
		$ena=$_POST["ena"]*1;
		$proxy=$_POST["proxy"]*1;
		$fou=$_POST["ou"]*1;
		$fgrp=$_POST["group"]*1;
	        $flhour=mysql_escape_string(substr($_POST["flhour"],0,5));
    	        $flday=mysql_escape_string(substr($_POST["flday"],0,5));
	        $flmonth=mysql_escape_string(substr($_POST["flmonth"],0,5));
	        if (!($ena or $nat)) { $ficq=0; }
	    	    else { $ficq=$_POST["icq"]*1; }
		
		mysql_query("update User_list set Login='$login',fio='$fio',
			    email='$email',nat='$nat',enabled='$ena',
			    proxy='$proxy',perhour='$flhour',perday='$flday',icq='$ficq',
			    permonth='$flmonth',OU_id='$fou',Group_id='$fgrp' where id='$id'");
			    
		mysql_query("update User_auth set nat='$nat',enabled='$ena',proxy='$proxy' where userid='$id'");
		header("location: index.php");
		}
	
	if ($_POST["addauth"]) {
		$id=$_POST["id"]*1;
		$fip=mysql_escape_string(substr($_POST["newip"],0,16));
		if ($fip) {
            	    if (checkValidIp($fip)) {
		    list($lid)=mysql_fetch_array(mysql_query("Select userid from User_auth where IP='$fip' and deleted=0"));
		    if ($lid>0) {
		    list($lname)=mysql_fetch_array(mysql_query("Select Login from User_list where id=$lid"));
		    $msg_error = "Адрес $fip уже есть! Принадлежит пользователю $lname.";
		    unset($_POST);
		    }
		    else {
		    mysql_query("insert into User_auth (IP,userid) values('$fip','$id')");
            	    list($fid)=mysql_fetch_array(mysql_query("Select id from User_auth where IP='$fip' order by id DESC"));
		    }
		    }
		    else { $msg_error="Формат адреса не верен! xxx.xxx.xxx.xxx/xx";  }
		}
 	}	
	

        if ($_POST["removeauth"]) {
        	$fgid = $_POST["fgid"];
                while (list ($key,$val) = @each ($fgid)) {
		if ($val) {
            	    list($tcount)=mysql_fetch_array(mysql_query("Select count(T.userid) from User_traffic T where T.userid=$val"));
		    if ($tcount==0) {
	        	    mysql_query("delete from User_filters where UserId=".$val);
	        	    mysql_query("delete from User_auth where Id=".$val);
	            }
		    else    { mysql_query("Update User_auth set deleted=1 where id=$val");   }
		    }
		}
	}		     

        unset($_POST); 
        
	list($login,$fio,$email,$nat,$ena,$proxy,$blocked,$fou,$fgroup,$flhour,$flday,$flmonth,$ficq)=mysql_fetch_array(mysql_query("select login, fio, email, nat, enabled, proxy,blocked, ou_id, group_id, perhour, perday, permonth, icq  from User_list where id=$id"));
	
	
	require_once("../../inc/header.php");
?>
	<div id="cont">

	<form name="def" action="edituser.php?id=<? echo $id; ?>" method="post">
	<input type="hidden" name="id" value=<? echo $id; ?>>
        <table  class="data" cellspacing="1" cellpadding="4">
        <tr>
        <td >Login</td>
        <td >ФИО</td>
        <td >Почта</td>
	</tr>
	<tr>
        <td><input type="text" name="login" value="<? echo $login; ?>" size=25></td>
        <td><input type="text" name="fio" value="<? echo $fio; ?>" size=25></td>
        <td><input type="text" name="email" value="<? echo $email; ?>" size=25></td>
	</tr>
	<tr>
        <td >Организация</td>
        <td >Фильтры</td>
        <td >НАТ</td>
        <td >Icq</td>
        <td >Прокси</td>
        <td >Включен</td>
        <td >Заблокирован</td>
	</tr>
	<tr>
        <td>
	<select name="ou">
<?
        $r_ou=mysql_query("SELECT * FROM OU Order by name");
        while (list($fid,$fname)=mysql_fetch_array($r_ou)) {
        if ($fid==($fou*1)) { print "<option value=$fid selected>$fname</option>";}
                else { print "<option value=$fid>$fname</option>"; }
        }
?>
        </select>
	</td>
        <td>
	<select name="group">
<?
        $f_ou=mysql_query("SELECT * FROM Group_list Order by Groupname");
        while (list($fid,$fname)=mysql_fetch_array($f_ou)) {
        if ($fid==($fgroup*1)) { print "<option value=$fid selected>$fname</option>"; }
                else { print "<option value=$fid>$fname</option>"; }
        }
?>
	</td>
        <td><input type="text" name="nat" value="<? echo $nat; ?>" size=1></td>
        <td><input type="text" name="icq" value="<? echo $ficq; ?>" size=1></td>
        <td><input type="text" name="proxy" value="<? echo $proxy; ?>" size=1></td>
        <td><input type="text" name="ena" value="<? echo $ena; ?>" size=1></td>
        <td><input type="text" name="block" value="<? echo $blocked; ?>" size=1></td>
        </tr>
        <tr>
        <td>В час</td>
        <td>в день</td>
        <td>в месяц</td>
        </tr>
        <tr>
        <td><input type="text" name="flhour" value="<? echo $flhour; ?>" size=5></td>
        <td><input type="text" name="flday" value="<? echo $flday; ?>" size=5></td>
        <td><input type="text" name="flmonth" value="<? echo $flmonth; ?>" size=5></td>
        </tr>
	<tr><td><input type="submit" name="edituser" value="Сохранить"></td></tr>
	</table>
	<br>
	
<?    if ($msg_error) { print "<div id='msg'><b>$msg_error</b></div><br>\n"; } ?>
	
	<b>Список адресов доступа</b><br>
        <table  class="data" cellspacing="1" cellpadding="4">
	<tr><td class="data"><input type="checkbox" onClick="checkAll(this.checked);"></a></td>
	<td class="data">IP</td>
	<td class="data">Mac</td>
	<td class="data">Комментарий</td>
	<td class="data">proxy</td>
	<td class="data">nat</td>
	<td class="data">ФильтрГруппы</td>
	<td class="data">Vkontakte</td>
	<td class="data">включен</td>
	<td class="data"><b>удалён</b></td>
	<td class="data">Transparent</td>
	<td class="data">Bandwidth</td>
        <td align="right"><input type="submit" name="removeauth" value="Удалить"></td>
	</tr>
<?
	$flist = mysql_query("SELECT id, IP, mac, nat, proxy, enabled, grpflt, deleted, comments, transparent, bandwidth, vkontakte  from User_auth WHERE userid=$id and deleted=0");
	while (list($fgid,$flogin,$fmac,$fnat,$fproxy,$fena,$fgrpflt,$fdel,$fcomm, $ftr, $fband, $fvk)=mysql_fetch_array($flist)) {
        print "<tr align=center>\n";
        print "<td class=\"data\" style='padding:0'><input type=checkbox name=fgid[] value=$fgid ></td>\n";
        print "<td class=\"data\" align=left><a href=editauth.php?id=$fgid>".$flogin."</a></td>\n";
        print "<td class=\"data\" >$fmac</td>\n";
        print "<td class=\"data\" >$fcomm</td>\n";
        print "<td class=\"data\" >$fproxy</td>\n";
        print "<td class=\"data\" >$fnat</td>\n";
	print "<td class=\"data\" >$fgrpflt</td>\n";
	print "<td class=\"data\" >$fvk</td>\n";
        print "<td class=\"data\" >$fena</td>\n";
	print "<td class=\"data\" >$fdel</td>\n";
	print "<td class=\"data\" >$ftr</td>\n";
	print "<td class=\"data\" >$fband</td>\n";
	print "<td class=\"data\" ></td>\n";
	print "</tr>";
        }
?>
        </tr></table>
        <table  class="data" cellspacing="1" cellpadding="4" width="550">
        <tr><td>
	<input type="submit" name="addauth" value="Добавить IP" >
	</td>
	<td><input type=text name=newip value="0.0.0.0"></td>
        </tr>
	</table>
	</form>
<?
	require_once("../../inc/footer.php");
?>