<?php
  require_once("../../inc/auth.php");
  require_once("../../inc/languages/".$language.".php");
  require_once("../../inc/header.php");

  list($fmonth,$fyear)=explode(" ",date("n Y",time()));
  
  $ryear=$fyear;
  $rmonth=$fmonth;  
  if (isset($_POST['year'])) { $ryear=$_POST["year"]*1; }
	
  if (isset($_POST['month'])) { $rmonth=$_POST["month"]*1; }

?>
  <div id="cont">
  <table cellspacing="0" cellpadding="0"><tr><td>
  <b>Статистика по интерфейсам За месяц | <a href="period.php"> За период </a></b>
  <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
  <input type="hidden" name="id" value=<? echo $id; ?>>
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
SUM(`eth_wan`) as fout,
SUM(`wan_eth`) as fin
FROM wan_traffic where (YEAR(`Date`)=$ryear and MONTH(`Date`)=$rmonth)
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

$wan1s=mysql_query("SELECT DATE_FORMAT(`date`,'%Y-%m') as dat, 
SUM(`wan_in`) as wanin, SUM(`wan_out`) as wanout, 
SUM(`eth_wan`) as fout,
SUM(`wan_eth`) as fin
FROM wan_traffic where (YEAR(`Date`)=$ryear and MONTH(`Date`)=$rmonth)
GROUP by DATE_FORMAT(`date`,'%Y-%m')");

list($fd,$fwi,$fwo,$ffo,$ffi)=mysql_fetch_array($wan1s);

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