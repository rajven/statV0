<?php
  require_once("../../inc/auth.php");
  require_once("../../inc/languages/".$language.".php");
  require_once("../../inc/header.php");

  list($rday,$rmonth,$ryear)=explode(" ",date("j n Y",time()));

  if (isset($_POST['year'])) { $ryear=$_POST["year"]*1; }
  if (isset($_POST['month'])) { $rmonth=$_POST["month"]*1; }
  if (isset($_POST['day'])) { $rday=$_POST["day"]*1; }
  if (isset($_POST['ou'])) { $rou=$_POST["ou"]*1; } else { $rou=1;}

  if ($rmonth <10 ) { $rmonth="0$rmonth"; }
  if ($rday <10 ) { $rday="0$rday"; }
  $dt1 = "'$ryear-$rmonth-$rday'";
      
unset($_POST); 
    
?>
  <div id="cont">
  <b>Трафик За дату | <a href="monthreport.php"> за месяц </a></b> | <a href="period.php"> зА период </a>
  <form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    <b> Организация </b>
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
  
 <b>День</b>
  <select name="day">
<?
  for ($i=1;$i<=31;$i++) {
  if ($i==($rday*1)) { print "<option value=$i selected>$i</option>";}
      else { print "<option value=$i>$i</option>"; }
  }
?>
  </select>
  <b><? echo userinfo12; ?></b>
  <select name="month">
<?
  for ($i=1;$i<=12;$i++) {
  if ($i==($rmonth*1)) { print "<option value=$i selected>$m[$i]</option>";}
      else { print "<option value=$i>$m[$i]</option>"; }
  }
?>
  </select>
  <b><? echo userinfo11; ?></b>
  <select name="year">
<?
  $ryears=mysql_query("select year from years order by year");
  while (list($i)=mysql_fetch_array($ryears)) {
  if ($i==($ryear*1)) {
            print "<option value=$i selected>$i</option>";
            } else {
          print "<option value=$i>$i</option>";
      }
}
?>
  </select>
<input type="submit" value="OK">
  </form>
															        
  <br><br>
  <table class="data" width="650" cellspacing="1" cellpadding="4">
  <tr align="center">
    <td ><b>Логин</b></td>
    <td ><b><? echo sessions13; ?></b></td>
    <td ><b><? echo sessions14; ?></b></td>
  </tr>

<?  

$sSQL = "SELECT User_list.id,User_list.Login, SUM(tin) as traf, SUM(tout)
    FROM 
    (select userid,SUM(bytein) as tin,SUM(byteout) as tout from User_stats
    where (Date(`Dat`)=$dt1)
    GROUP by userid) as V, User_auth, User_list
    WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) ";

  if ($rou == 0) { $sSQL = $sSQL." GROUP by Login Order by traf DESC"; } 
      else { $sSQL = $sSQL." and User_list.OU_id=$rou  GROUP by Login Order by traf DESC"; }
    
  $users=mysql_query($sSQL);
    
  $total_in=0;
  $total_out=0;
  
  while (list($fid,$login,$traf_day_in,$traf_day_out)=mysql_fetch_array($users)) {
  if ($traf_day_in+$traf_day_out>0) {
    print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
    print "<td class=\"data\"><a href=userday.php?id=$fid&year=$ryear&month=$rmonth&day=$rday>$login</a></td>\n";
    print "<td class=\"data\">". fbytes($traf_day_in) ."</td>\n";
    print "<td class=\"data\">". fbytes($traf_day_out) ."</td>\n";
    print "</tr>\n";
    $total_in+=$traf_day_in;
    $total_out+=$traf_day_out;
    }
  }

  print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
  print "<td class=\"data\"><b>". sessions15 ."</b></td>\n";
  print "<td class=\"data\"><b>". fbytes($total_in) ."</b></td>\n";
  print "<td class=\"data\"><b>". fbytes($total_out) ."</b></td>\n";
  print "</tr>\n";
  
?>
  </table>
  
<?  
  
  require_once("../../inc/footer.php");
?>