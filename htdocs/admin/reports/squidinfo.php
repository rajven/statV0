<?php
  require_once("../../inc/auth.php");
  require_once("../../inc/languages/".$language.".php");
  require_once("../../inc/header.php");

  list($fday,$fmonth,$fyear)=explode(" ",date("j n Y",time()));
  
  if (isset($_GET['id'])) { $id=$_GET["id"]*1; }
  if (!isset($id)) { header("location: index.php"); }
	
  if (isset($_GET['year'])) { $ryear=$_GET["year"]*1; }
  if (isset($_GET['month'])) { $rmonth=$_GET["month"]*1; }
  
  if (isset($_GET['day'])) { $rday=$_GET["day"]*1; }
	else 
	{ $rday=0;}
		      
  if (!isset($ryear))  { $ryear=$fyear; }
  if (!isset($rmonth))  { $rmonth=$fmonth; }

  $fsort = "Order by bytes Desc";
  
  if (isset($_GET['sort'])) { 
	if ($_GET['sort']=="1" ) {$fsort = "Order by dt"; }
	}
  
  if ($rday=="0") { $fper=" месяц ".$rmonth.".".$ryear;      }
          else {  $fper=" дату ".$rday.".".$rmonth.".".$ryear; }
unset($_POST); 
	  			  
?>
			  
  <div id="cont">
  <form action="<?=$_SERVER['PHP_SELF']?>" method="GET">
  <input type="hidden" name="id" value=<? echo $id; ?>>
  <input type="hidden" name="year" value=<? echo $ryear; ?>>
  <input type="hidden" name="month" value=<? echo $rmonth; ?>>
  <input type="hidden" name="day" value=<? echo $rday; ?>>
  <table cellspacing="0" cellpadding="0">
<?
  $usersip=mysql_query("SELECT id,IP,userid FROM User_auth WHERE User_auth.id=$id");
  list($fid,$fip,$parent)=mysql_fetch_array($usersip);
  print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
  print "<td class=\"data\" ><b>Лог прокси-сервера для <a href=userinfo.php?id=$parent>$fip</a> за $fper</b></td>\n";
  print "<td class=\"data\" colspan=3>$fcomm</td>\n";
  print "<td class=\"data\" ><b> - сортировка - </b>";
?>
  <select name="sort"> 
  <option value=0> по трафику</option>
  <option value=1>по дате</option>
  </select> </td> <td><input type="submit" value="OK"></td>
  </tr></table><br>
  </form>
  <table class="data" cellspacing="1" cellpadding="4">
<?

if ($rday=="0") {
  $fsql = "SELECT dt,url,bytes FROM squid_log s where s.userid='$id'
  and ((YEAR(`Dt`)=$ryear) and (MONTH(`Dt`)=$rmonth))
  $fsort";}
  else {
  $fsql = "SELECT dt,url,bytes FROM squid_log s where s.userid='$id'
  and ((YEAR(`Dt`)=$ryear) and (MONTH(`Dt`)=$rmonth) and (DAY(`Dt`)=$rday))
  $fsort";}
?>
  <tr align="center">
  <td class="data"><b>Дата</b></td>
  <td class="data"><b>Url</b></td>
  <td class="data"><b>Байт</b></td>
  </tr>
<?
  $userdata=mysql_query($fsql); 
  while (list($dt,$url,$ubytes)=mysql_fetch_array($userdata)) {
    print "<tr align=left align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
    print "<td class=\"data\" >". $dt ."</td>\n";
    print "<td class=\"data\" >". $url ."</td>\n";
    print "<td class=\"data\" >". fbytes($ubytes) ."</td>\n";
    print "</tr>\n";
    }
?>
  </table>
<?  
  require_once("../../inc/footer.php");
?>