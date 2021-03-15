<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");
	
	$msg_error = "";
	
	if ($_POST["create"]) {
		$login=$_POST["newlogin"];
		if ($login) {
		list($lcount)=mysql_fetch_array(mysql_query("Select count(id) from Customers where LCase(Login)=LCase('$login')"));
		if ($lcount>0) {
		    $msg_error = "Логин $login в базе уже есть!";
		    unset($_POST);
		    }
		    else {
		    mysql_query("INSERT INTO Customers (Login) VALUES('$login')");
		    list($id)=mysql_fetch_array(mysql_query("Select id from Customers where Login='$login' order by id DESC"));
		    header("location: editcustom.php?id=$id");
		    }
		}
	}

	if ($_POST["remove"]) {
           $fid = $_POST["fid"];
           while (list ($key,$val) = @each ($fid)) {
           if ($val) {
		mysql_query("delete from Customers where id=".$val);
		mysql_query("delete from OU_customers where cid=".$val);
		}
	    }
	}
	unset($_POST); 
	require_once("../../inc/header.php");
	
?>
      <div id="cont">
<?    if ($msg_error) { print "<div id='msg'><b>$msg_error</b></div><br>\n"; } ?>
  
      <form name="def" action="index.php" method="post"> 
      <b>Список пользователей. </b>
     
     <table class="data" width="250" cellspacing="1" cellpadding="4">
	<tr align="center">
                <td width="30"><input type="checkbox" onClick="checkAll(this.checked);"></a></td>
		<td><b>Login</b></td>
	</tr>
<?

	$sSQL = "SELECT id, Login from Customers";

	$users=mysql_query($sSQL);
	
	while (list($id,$login)=mysql_fetch_array($users)) {
		$cl="data";
		print "<tr align=center>\n";
		print "<td class=\"$cl\" style='padding:0'><input type=checkbox name=fid[] value=$id></td>\n";
		print "<td class=\"$cl\" align=left><a href=editcustom.php?id=$id>".$login."</a></td>\n";
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