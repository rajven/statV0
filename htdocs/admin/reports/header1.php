<?
  list($fday,$fmonth,$fyear)=explode(" ",date("j n Y",time()));
  
  if (isset($_GET['id'])) { $id=$_GET["id"]*1; }
  if (isset($_POST['id'])) { $id=$_POST["id"]*1; }
      
  if (!isset($id)) { header("location: index.php"); }
	
  if (isset($_GET['year'])) { $ryear=$_GET["year"]*1; }
  if (isset($_GET['month'])) { $rmonth=$_GET["month"]*1; }
  if (isset($_GET['day'])) { $rday=$_GET["day"]*1; $fper=" дату ".$rday.".".$rmonth.".".$ryear; }
	else { $rday=$fday; $fper=" за месяц ".$rmonth.".".$ryear; 	}
		      
  if (!isset($ryear))  { $ryear=$fyear; }
  if (!isset($rmonth))  { $rmonth=$fmonth; }
			  
?>
		    
  <div id="cont"> 
  <table cellspacing="0" cellpadding="0">

<?
  $usersip=mysql_query("SELECT id,IP,userid FROM User_auth WHERE User_auth.id=$id");
  
  list($fid,$fip,$parent)=mysql_fetch_array($usersip);

  print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
  print "<td class=\"data\" ><b>Детализация для <a href=userinfo.php?id=$parent>$fip</a> за $fper</b></td>\n";
  print "<td class=\"data\" colspan=3>$fcomm</td>\n";
  print "</tr>\n</table><br>";
	      
?>		      
  <table class="data" width="400" cellspacing="1" cellpadding="4">
  <tr align="center">
  <td class="data" width=30 ><b>Протокол</b></td>
  <td class="data" width=150><b>Откуда</b></td>
  <td class="data" width=50 ><b>Порт</b></td>
  <td class="data" width=150><b>Байт</b></td>
  <td class="data" width=150><b>free</b></td>
  </tr>
							    