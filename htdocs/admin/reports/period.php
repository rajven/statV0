<?php
  require_once("../../inc/auth.php");
  require_once("../../inc/languages/".$language.".php");
  require_once("../../inc/header.php");

  list($fday,$fmonth,$fyear)=explode(" ",date("j n Y",time()));
  
  if (isset($_POST['y1'])) { $ry1=$_POST["y1"]*1; }
	else  { $ry1=$fyear; }
  if (isset($_POST['m1'])) { $rm1=$_POST["m1"]*1; }
	else { $rm1=$fmonth-1; }
  if (isset($_POST['d1'])) { $rd1=$_POST["d1"]*1; }
	else { $rd1=21*1; }

  if (isset($_POST['y2'])) { $ry2=$_POST["y2"]*1; }
	else  { $ry2=$fyear; }
  if (isset($_POST['m2'])) { $rm2=$_POST["m2"]*1; }
	else { $rm2=$fmonth; }
  if (isset($_POST['d2'])) { $rd2=$_POST["d2"]*1; }
	else { $rd2=21; }

  $rou=0;


  if ($rm1 <10 ) { $rm1="0$rm1"; }
  if ($rd1 <10 ) { $rd1="0$rd1"; }
  if ($rm2 <10 ) { $rm2="0$rm2"; }
  if ($rd2 <10 ) { $rd2="0$rd2"; }
  
  $dt1 = "'$ry1-$rm1-$rd1'";
  $dt2 = "'$ry2-$rm2-$rd2'";
  
  if (isset($_POST['ou'])) { $rou=$_POST["ou"]*1; } 
unset($_POST); 
 			 
?>
  <div id="cont">
  <table cellspacing="0" cellpadding="0"><tr><td>
  <b>Статистика  <a href="index.php"> За сегодня </a>| <a href="monthreport.php"> За месяц </a> | <b> За период </b>
  <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
  <input type="hidden" name="id" value=<? echo $id; ?>>
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
  Период: 
  <b>день</b>
  <select name="d1">
<?
  for ($i=1;$i<=31;$i++) {
  if ($i==($rd1*1)) { print "<option value=$i selected>$i</option>";}
            else { print "<option value=$i>$i</option>"; }
    }
?>
  </select>
  
  <b><? echo userinfo12; ?></b>
  <select name="m1">
<?
  for ($i=1;$i<=12;$i++) {
  if ($i==($rm1*1)) { print "<option value=$i selected>$m[$i]</option>";}
            else { print "<option value=$i>$m[$i]</option>"; }
    }
?>
  </select>
  <b><? echo userinfo11; ?></b>
  <select name="y1">
<?
  $ryears=mysql_query("select year from years order by year");
  while (list($i)=mysql_fetch_array($ryears)) {
  if ($i==($ry1*1)) {
              print "<option value=$i selected>$i</option>";
                } else {
          print "<option value=$i>$i</option>";
        }
  }
?>
  </select>
  ПО Дату:

  <select name="d2">
<?
  for ($i=1;$i<=31;$i++) {
  if ($i==($rd2*1)) { print "<option value=$i selected>$i</option>";}
            else { print "<option value=$i>$i</option>"; }
    }
?>
  </select>
  
  <b><? echo userinfo12; ?></b>
  <select name="m2">
<?
  for ($i=1;$i<=12;$i++) {
  if ($i==($rm2*1)) { print "<option value=$i selected>$m[$i]</option>";}
            else { print "<option value=$i>$m[$i]</option>"; }
    }
?>
  </select>
  <b><? echo userinfo11; ?></b>
  <select name="y2">
<?
  $ryears=mysql_query("select year from years order by year");
  while (list($i)=mysql_fetch_array($ryears)) {
  if ($i==($ry2*1)) {
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
    <td ><b><? echo sessions9; ?></b></td>
    <td ><b><? echo sessions13; ?></b></td>
    <td ><b><? echo sessions14; ?></b></td>
  </tr>

<?  

$sSQL = "SELECT User_list.id,User_list.Login, SUM(tin) as traf, SUM(tout) FROM (select userid,SUM(bytein) as tin, SUM(byteout) as tout
    from User_stats where (`Dat`>=$dt1 and `Dat`<$dt2)
    GROUP by userid) as V, User_auth, User_list
    WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id)";

  if ($rou == 0) { $sSQL = $sSQL." GROUP by Login Order by traf DESC"; }
        else { $sSQL = $sSQL." and (User_list.OU_id=$rou)  GROUP by Login Order by traf DESC"; }

  $users=mysql_query($sSQL);
  
  $total_in=0;
  $total_out=0;

  while (list($fid,$login,$traf_month_in,$traf_month_out)=mysql_fetch_array($users)) {
  
  if ($traf_month_in+$traf_month_out>0) {
    print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
    print "<td class=\"data\"><a href=userinfo.php?id=$fid&year=$ryear&month=$rmonth>$login</a></td>\n";
    print "<td class=\"data\">". fbytes($traf_month_in) ."</td>\n";
    print "<td class=\"data\">". fbytes($traf_month_out) ."</td>\n";
    print "</tr>\n";
    $total_in+=$traf_month_in;
    $total_out+=$traf_month_out;
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