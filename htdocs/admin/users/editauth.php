<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");

	$id=$_GET["id"]*1;
	if (!$_POST["id"] && !$id) {
		header("location: index.php");
	}

	$msg_error = "";
	
	if ($_POST["editauth"]) {
		$id=$_POST["id"]*1;
		$fip=mysql_escape_string(substr($_POST["fip"],0,20));
		$fcomm=mysql_escape_string(substr($_POST["fcomm"],0,20));
		
		if (checkValidIp($fip))  {
		    $fgrpflt=$_POST["fgrpflt"]*1;
		    $fnat=$_POST["fnat"]*1;
		    $fmac=mysql_escape_string(substr($_POST["fmac"],0,20));
		    if (!IsMacValid($fmac)) { $fmac=''; }
		    $fena=$_POST["fena"]*1;
		    $fproxy=$_POST["fproxy"]*1;
		    $fdel=$_POST["fdel"]*1;
		    $ftr=$_POST["ftr"]*1;
		    $fband=$_POST["fband"]*1;
		    $fvk=$_POST["fvk"]*1;
		    if ($fband<16) { $fband=0; }
		    mysql_query("update User_auth set nat='$fnat',mac='$fmac',grpflt='$fgrpflt',enabled='$fena',proxy='$fproxy',IP='$fip',comments='$fcomm',deleted='$fdel',transparent='$ftr',bandwidth='$fband',vkontakte='$fvk' where id='$id'");
		    }
		else {
		$msg_error="Формат адреса не верен! xxx.xxx.xxx.xxx/xx";
		}
		}
	if ($_POST["moveauth"]) {
		$id=$_POST["id"]*1;
		$fui=$_POST["newparent"];
		mysql_query("update User_auth set userid='$fui' where id='$id'");
		}

	list($ip,$mac,$nat,$ena,$proxy,$grpflt,$deleted,$parent,$comm,$tr,$band,$vk)=mysql_fetch_array(mysql_query("select ip, mac, nat, enabled, proxy, grpflt, deleted, userid, comments, transparent, bandwidth, vkontakte  from User_auth where id=$id"));
 	list($parentname)=mysql_fetch_array(mysql_query("select Login from User_list where id=$parent"));

        if ($_POST["addfilter"]) {
	                $id=$_POST["id"]*1;
	                $fid=$_POST["newfilter"]*1;
	                mysql_query("insert into User_filters (userid,FilterId) values('$id','$fid')");
		        }
	
        if ($_POST["removefilter"]) {
                     $fid = $_POST["id"]*1;
                     $fgid=$_POST["ffid"];
                     while (list ($key,$val) = @each ($fgid)) {
                      if ($val) {
                               mysql_query("delete from User_filters where Id=".$val*1);
                            }
                     }
         }

	
	require_once("../../inc/header.php");
?>
	<div id="cont">

<?	print "<b> Адрес доступа пользователя <a href=/admin/users/edituser.php?id=$parent>$parentname</a> <b>";?>
	<form name="def" action="editauth.php?id=<? echo $id; ?>" method="post">
	<input type="hidden" name="id" value=<? echo $id; ?>>
        <table  class="data" cellspacing="1" cellpadding="4">
        <tr>
        <td>IP</td>
        <td>MAC</td>
        <td>Комментарий</td>
        <td>NAT</td>
        <td>Прокси</td>
	<td>Vkontakte</td>
        <td>Включен</td>
	<td>ФильтрГруппы</td>
	<td><b>удалён</b></td>
	<td>Transparent</td>
	<td>Bandwidth</td>
	</tr>
	<tr>
        <td><input type="text" name="fip" value="<? echo $ip; ?>" size=16></td>
        <td><input type="text" name="fmac" value="<? echo $mac; ?>" size=17></td>
        <td><input type="text" name="fcomm" value="<? echo $comm; ?>" size=20></td>
        <td><input type="text" name="fnat" value="<? echo $nat; ?>" size=1></td>
        <td><input type="text" name="fproxy" value="<? echo $proxy; ?>" size=1></td>
        <td><input type="text" name="fvk" value="<? echo $vk; ?>" size=1></td>
        <td><input type="text" name="fena" value="<? echo $ena; ?>" size=1></td>
        <td><input type="text" name="fgrpflt" value="<? echo $grpflt; ?>" size=1></td>
        <td><input type="text" name="fdel" value="<? echo $deleted; ?>" size=1></td>
        <td><input type="text" name="ftr" value="<? echo $tr; ?>" size=1></td>
        <td><input type="text" name="fband" value="<? echo $band; ?>" size=10></td>
        </tr>
	<tr>
	<td colspan=6>
	<input type="submit" name="editauth" value="Сохранить">
	<input type="submit" name="moveauth" value="Переместить">
        <select name="newparent">
<?
        $ulist = mysql_query("SELECT id,Login FROM User_list WHERE id not in (Select userid from User_auth where id=$id) order by Login");
        while (list($fui,$fun)=mysql_fetch_array($ulist)) {
	print "<option value=$fui>$fun</option>";
        }
?>
        </select>
	</td>
	</tr>
	</table>

<?    if ($msg_error) { print "<div id='msg'><b>$msg_error</b></div><br>\n"; } ?>

	<br><b>Список персональных фильтров</b><br>
        <table  class="data" cellspacing="1" cellpadding="4">
	<tr><td>Код</td><td>Фильтр</td>
        <td align="right"><input type="submit" name="removefilter" value="Удалить"></td>
	</tr>
<?
	$flist = mysql_query("SELECT U.id, f.Name, f.id  FROM User_filters U, filter_list f WHERE f.id=U.Filterid and userid=$id");
	while (list($ffid,$ffname,$fti)=mysql_fetch_array($flist)) {
        print "<tr align=center>\n";
        print "<td class=\"data\" style='padding:0'><input type=checkbox name=ffid[] value=$ffid ></td>\n";
        print "<td class=\"data\" align=left><a href=/admin/filters/editfilter.php?id=$fti>".$ffname."</a></td>\n";
	print "<td class=\"data\"></td>\n";
	print "</tr>";
        }
?>
        </tr></table>
        <table  class="data" cellspacing="1" cellpadding="0" width="550">
        <tr><td>
        <input type="submit" name="addfilter" value="Добавить фильтр" >
        <select name="newfilter">
<?
        $filters = mysql_query("SELECT f.id,Name FROM filter_list f WHERE f.id not in (Select FilterId from User_filters where userid=$id)");
        while (list($fni,$fname)=mysql_fetch_array($filters)) {
        print "<option value=$fni>$fname</option>";
         }
?>
        </select>
	</table>
	</form>
<?
	require_once("../../inc/footer.php");
?>