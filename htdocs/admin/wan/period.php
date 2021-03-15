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

  $dt1 = "'$ry1-$rm1-$rd1'";
  $dt2 = "'$ry2-$rm2-$rd2'";
  

?>
  <div id="cont">
  <table cellspacing="0" cellpadding="0"><tr><td>
  <b>Статистика по интерфейсам <a href="index.php"> За месяц </a> | За период </b>
  <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
  <input type="hidden" name="id" value=<? echo $id; ?>>
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
  <br>
  <table class="data" width="650" cellspacing="1" cellpadding="4">
  <tr align="center">
    <td ><b>Дата</b></td>
    <td ><b>Wan IN</b></td>
    <td ><b>Wan Out</b></td>
    <td ><b>Forward IN</b></td>
    <td ><b>Forward Out</b></td>
    <td ><b>SUM IN</b></td>
    <td ><b>SUM Out</b></td>
  </tr>
<?  

$ssql = "SELECT DATE_FORMAT(`date`,'%Y-%m-%d') as dat, 
SUM(`wan_in`) as wanin, SUM(`wan_out`) as wanout, 
SUM(`eth_wan`) as fout, SUM(`wan_eth`) as fin
FROM wan_traffic where  (`date`>=$dt1 and `date`<$dt2)
GROUP by DATE_FORMAT(`date`,'%Y-%m-%d')";

$wans=mysql_query($ssql);

while (list($fd,$fwi,$fwo,$ffo,$ffi)=mysql_fetch_array($wans))
{
print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
print "<td class=\"data\">$fd</td>\n";
print "<td class=\"data\">". fbytes($fwi) ."</td>\n";
print "<td class=\"data\">". fbytes($fwo) ."</td>\n";
print "<td class=\"data\">". fbytes($ffi) ."</td>\n";
print "<td class=\"data\">". fbytes($ffo) ."</td>\n";
print "<td class=\"data\">". fbytes($ffi+$fwi) ."</td>\n";
print "<td class=\"data\">". fbytes($ffo+$fwo) ."</td>\n";
print "</tr>\n";
}

$wan1s=mysql_query("SELECT SUM(`wan_in`) as wanin, SUM(`wan_out`) as wanout, 
(SUM(`eth_wan`)) as fout, (SUM(`wan_eth`)) as fin 
FROM wan_traffic where (`date`>=$dt1 and `date`<$dt2)");

list($fwi,$fwo,$ffo,$ffi)=mysql_fetch_array($wan1s);

print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
print "<td> Итог: </td>\n";
print "<td >". fbytes($fwi) ."</td>\n";
print "<td >". fbytes($fwo) ."</td>\n";
print "<td >". fbytes($ffi) ."</td>\n";
print "<td >". fbytes($ffo) ."</td>\n";
print "<td >". fbytes($ffi+$fwi) ."</td>\n";
print "<td >". fbytes($ffo+$fwo) ."</td>\n";
print "</tr>\n";

?>
  </table>
  </form>
  
<?
  require_once("../../inc/footer.php");
?> 

