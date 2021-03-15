<?php
  require_once("../../inc/auth.php");
  require_once("../../inc/languages/".$language.".php");
  require_once("../../inc/header.php");
  require_once("../reports/header1.php");

  if ($rmonth <10 ) { $rmonth="0$rmonth"; }
  if ($rday <10 ) { $rday="0$rday"; }
  $dt1 = "'$ryear-$rmonth-$rday'";
      
      
  $fsql = "SELECT A.proto, A.srcip, A.srcport, SUM(A.bytes) as tin, A.free FROM All_traf A
            WHERE (dstip='$fip') and (prefix='FORWARD') and (Date(`Dt`)=$dt1)
                GROUP BY A.srcip, A.srcport, A.proto Order by tin DESC LIMIT 0,30";
                  
  $userdata=mysql_query($fsql); 

  while (list($uproto,$uip,$uport,$ubytes,$ufree)=mysql_fetch_array($userdata)) {
  
    print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
    print "<td class=\"data\">". $uproto ."</td>\n";
    print "<td class=\"data\" align=left>". $uip ."</td>\n";
    print "<td class=\"data\">". $uport ."</td>\n";
    print "<td class=\"data\" align=right>". fbytes($ubytes) ."</td>\n";
    print "<td class=\"data\" align=right>". $ufree ."</td>\n";
    print "</tr>\n";
    }

?>
  </table>
  <br>
  <b>Трафик через прокси (ТОП 30)</b> Полный лог смотреть 
<?  print "<a href=squidinfo.php?id=$id&year=$ryear&month=$rmonth&day=$rday>здесь</a>"; ?>
  <br>
  <table class="data" cellspacing="1" cellpadding="4">
  <tr align="center">
  <td class="data"><b>Сервер</b></td>
  <td class="data"><b>Байт</b></td>
  </tr>
<?
  $fsql = "SELECT SUM(bytes) as size,server FROM squid_log s where s.userid='$id'
  and ((YEAR(`Dt`)=$ryear) and (MONTH(`Dt`)=$rmonth) and (DAY(`Dt`)=$rday))
  GROUP BY s.server ORDER by size DESC LIMIT 0,30";
  
  $userdata=mysql_query($fsql); 

  while (list($ubytes,$userver)=mysql_fetch_array($userdata)) {
  
    print "<tr align=left align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
    print "<td class=\"data\">". $userver ."</td>\n";
    print "<td class=\"data\" align=right>". fbytes($ubytes) ."</td>\n";
    print "</tr>\n";
    }
?>
  </table>
<?  
  require_once("../../inc/footer.php");
?>