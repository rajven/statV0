<?php
  require_once("../../inc/auth.php");
  require_once("../../inc/languages/".$language.".php");
  list($hour,$day,$month,$year)=explode(" ",date("H j n Y",time()));
  require_once("../../inc/header.php");
  require_once("../reports/footer.php");
      
  $usersip=mysql_query("SELECT id,IP,comments FROM User_auth WHERE (User_auth.userid=$id) Order by IP"); 

  $ipcount = 0;
  $itog_in = 0;
  $itog_out = 0;
        
  while ($row=mysql_fetch_array($usersip)) {
  
  $fid = $row["id"];$fip = $row["IP"];$fcomm= $row["comments"];
  $ipcount++;
  print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
  print "<td class=\"data\" ><b><a href=../users/editauth.php?id=$fid>$fip</a></b></td>\n";
  print "<td class=\"data\" colspan=3>$fcomm</td>\n";
  print "</tr>\n";
		
  $userdata=mysql_query("SELECT DATE_FORMAT(`Dat`,'%Y-%m'),SUM(bytein),SUM(byteout),DATE_FORMAT(`Dat`,'%m') from User_stats
  where ((YEAR(`Dat`)=$ryear) and userid=$fid)
  GROUP BY DATE_FORMAT(`Dat`,'%Y-%m')"); 
  
  $sum_in = 0;
  $sum_out = 0;
    
  while (list($udata,$uin,$uout,$fm)=mysql_fetch_array($userdata)) {
  
    print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
    print "<td class=\"data\"> </td>\n";
    print "<td class=\"data\"> <a href=userinfo.php?id=$id&year=$ryear&month=$fm>".$udata."</a></td>\n";
    print "<td class=\"data\">". fbytes($uin) ."</td>\n";
    print "<td class=\"data\">". fbytes($uout) ."</td>\n";
    print "</tr>\n";
    $sum_in+=$uin; $sum_out+=$uout;
    }
    
      print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
      print "<td class=\"data\"><b>". userinfo9 ."</b></td>\n";
      print "<td class=\"data\"><b> </b></td>\n";
      print "<td class=\"data\"><b>". fbytes($sum_in) ."</b></td>\n";
      print "<td class=\"data\"><b>". fbytes($sum_out) ."</b></td>\n";
      print "</tr>\n";
      $itog_in+=$sum_in;
      $itog_out+=$sum_out;
  }
      if ($ipcount>1) {
        print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
        print "<td class=\"data\"><b>". userinfo9 ."</b></td>\n";
        print "<td class=\"data\"><b> </b></td>\n";
        print "<td class=\"data\"><b>". fbytes($itog_in) ."</b></td>\n";
        print "<td class=\"data\"><b>". fbytes($itog_out) ."</b></td>\n";
        print "</tr>\n";}
?>
  </table>

  <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
  <input type="hidden" name="id" value=<? echo $id; ?>>
  <b><? echo userinfo11; ?></b>
  <select name="year">
<?
  $years=mysql_query("select year from years order by year");
   while (list($i)=mysql_fetch_array($years)) {
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
										      
<?  
  require_once("../../inc/footer.php");
?>