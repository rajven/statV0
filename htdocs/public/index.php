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
                                                                                                                                                             
    list($fday,$fmonth,$fyear)=explode(" ",date("j n Y",time()));                                                                                            
                                                                                                                                                             
    if (isset($_GET['year'])) { $ryear=$_GET["year"]*1; }                                                                                                    
    if (isset($_GET['month'])) { $rmonth=$_GET["month"]*1; }                                                                                                 
    if (isset($_GET['day'])) { $rday=$_GET["day"]*1; }                                                                                                       
                                                                                                                                                             
    if (isset($_POST['year'])) { $ryear=$_POST["year"]*1; }                                                                                                  
    if (isset($_POST['month'])) { $rmonth=$_POST["month"]*1; }                                                                                               
    if (isset($_POST['day'])) { $rday=$_POST["day"]*1; }                                                                                                     
                                                                                                                                                             
    if (!isset($ryear))  { $ryear=$fyear; }                                                                                                                  
    if (!isset($rmonth))  { $rmonth=$fmonth; }                                                                                                               
    if (!isset($rday))  { $rday=$fday; }                                                                                                                     
                                                                                                                                                             
    if ($ip=="unknown") { print "Error detecting user!!!"; }                                                                                                 
                                                                                                                                                             
    list($id)=mysql_fetch_array(mysql_query("SELECT userid FROM User_auth WHERE IP='$ip' and deleted=0"));                                                   
                                                                                                                                                             
    if ($id=="0") { print "Client $ip not found!"; };                                                                                                        
                                                                                                                                                             
    print "<div id=\"cont\"> <table cellspacing=0 cellpadding=0><tr><td><b>"; echo userinfo1; print " $login </b>";                                          
                                                                                                                                                             
    list($login,$blocked,$enabled)=mysql_fetch_array(mysql_query("SELECT Login,blocked,enabled FROM User_list WHERE User_list.id=$id"));                     
                                                                                                                                                             
    if (($blocked) or ($enabled=0)) {                                                                                                                        
            if ($blocked=1) {                                                                                                                                
                    print "<b> (Пользователь заблокирован по трафику!)</b><br>\n";                                                                           
                    }                                                                                                                                        
                    elseif ($enabled=0) {                                                                                                                    
                    print "<b> (Доступ запрещён администратором!)</b><br>\n";                                                                                
                    }                                                                                                                                        
            }                                                                                                                                                
            elseif ($proxy=0) {                                                                                                                              
                    print "<b>(Доступ через proxy-сервер не разрешён!)</b><br>\n";                                                                           
            }                                                                                                                                                
                                                                                                                                                             
    print "<a href=index.php>"; echo userinfo4; print "</a> | ";                                                                                             
    print "<a href=userday.php>"; echo userinfo6; print "</a></tr>";                                                                                         

?>
    <table class="data" width="650" cellspacing="1" cellpadding="4">                                                                                         
    <tr align="center">                                                                                                                                      
    <td class="data" ><b><? echo userinfo2; ?></b></td>                                                                                                      
    <td class="data" ><b><? echo userinfo10; ?></b></td>                                                                                                     
    <td class="data" ><b><? echo userinfo7; ?></b></td>                                                                                                      
    <td class="data" ><b><? echo userinfo8; ?></b></td>                                                                                                      
    </tr>                                                                                                                                                    
<?
    $usersip=mysql_query("SELECT id,IP,comments,userid FROM User_auth WHERE IP='$ip' and deleted=0");

    $ipcount = 0;
	
    while ($row=mysql_fetch_array($usersip)) {
	    
    $fid = $row["id"];$fip = $row["IP"];$fcomm= $row["comments"];$userid=$row["userid"];
    
    $userstat=mysql_query("SELECT enabled, blocked FROM User_list WHERE (User_list.id=$fid)");
    $row2=mysql_fetch_array($userstat);
    $fena=$row2["enabled"]; $fblock=$row2["blocked"];
    
    $ipcount++;
    print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
    print "<td class=\"data\" ><b>$fip</b></td>\n";
    print "<td class=\"data\" colspan=2>$fcomm</td>\n";
    print "<td class=\"data\"></td>\n";
    print "</tr>\n";
				    
  $userdata=mysql_query("SELECT DATE_FORMAT(`Dat`,'%Y-%m-%d'),SUM(bytein),SUM(byteout),DATE_FORMAT(`Dat`,'%d')  from User_stats
  where ((YEAR(`Dat`)=$ryear) and (MONTH(`Dat`)=$rmonth) and userid=$fid)
  GROUP BY DATE_FORMAT(`Dat`,'%Y-%m-%d')"); 

  $sum_in = 0;
  $sum_out = 0;
  
  while (list($udata,$uin,$uout,$fday)=mysql_fetch_array($userdata)) {
  
    print "<tr align=center align=center class=\"tr1\" onmouseover=\"className='tr2'\" onmouseout=\"className='tr1'\">\n";
    print "<td class=\"data\"> </td>\n";
    print "<td class=\"data\"> <a href=./userday.php?year=$ryear&month=$rmonth&day=$fday>". $udata ."</a></td>\n";
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
  require_once("../inc/footer.php");
?>
