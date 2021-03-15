<?php
    define("CONFIG",1);
    require_once("../inc/config.php");
    define("SQL",1);
    require_once("../inc/languages/russian.php");
    require_once("../inc/sql.php");
    require_once("../inc/header_public.php");

    $ip = getenv("HTTP_CLIENT_IP");
    if (empty($ip)) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
	        if (empty($ip)) {
            $ip = getenv("REMOTE_ADDR");
            if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
                if (empty($ip)) { $ip = "unknown"; }
            }
        }
    }
															    
  if ($ip=="unknown") { print "Error detecting user!!!"; }
  
  list($fid,$parent)=mysql_fetch_array(mysql_query("SELECT id,userid FROM User_auth WHERE IP='$ip' and deleted=0 and enabled=1"));
  list($login)=mysql_fetch_array(mysql_query("SELECT Login FROM User_list WHERE User_list.id=$parent"));

  list($fday,$fmonth,$fyear)=explode(" ",date("j n Y",time()));
  
  if (isset($_GET['year'])) { $ryear=$_GET["year"]*1; }
  if (isset($_GET['month'])) { $rmonth=$_GET["month"]*1; }

  if (!isset($ryear))  { $ryear=$fyear; }
  if (!isset($rmonth))  { $rmonth=$fmonth; }

  if (isset($_GET['day'])) { $rday=$_GET["day"]*1; $fper=" дату ".$rday.".".$rmonth.".".$ryear; }
	else { $rday=$fday; $fper=" за месяц ".$rmonth.".".$ryear; 	}

?>
		    
  <div id="cont"> 
  <table cellspacing="0" cellpadding="0">

<?

  print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
  print "<td class=\"data\" ><b>Детализация для <a href=./index.php>$ip</a> за $fper</b></td>\n";
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
<?
  $fsql = "SELECT A.proto, A.srcip, A.srcport, SUM(A.bytes) as tin, A.free FROM All_traf A
    WHERE (dstip='$ip') and (prefix='FORWARD') and
    ((YEAR(`Dt`)=$ryear) and (MONTH(`Dt`)=$rmonth) and (DAY(`Dt`)=$rday))
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
  <b>Трафик через прокси (ТОП 30)</b> 
  <br>
  <table class="data" cellspacing="1" cellpadding="4">
  <tr align="center">
  <td class="data"><b>Сервер</b></td>
  <td class="data"><b>Байт</b></td>
  </tr>
<?
  $fsql = "SELECT SUM(bytes) as size,server FROM squid_log s where s.userid='$fid'
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
  require_once("../inc/footer.php");
?>							    