<?
  list($fday,$fmonth,$fyear)=explode(" ",date("j n Y",time()));
  
  if (isset($_GET['id'])) { $id=$_GET["id"]*1; }
  if (isset($_POST['id'])) { $id=$_POST["id"]*1; }
      
  if (!isset($id)) { header("location: index.php"); }
	
  if (isset($_GET['year'])) { $ryear=$_GET["year"]*1; }
  if (isset($_GET['month'])) { $rmonth=$_GET["month"]*1; }
  if (isset($_GET['day'])) { $rday=$_GET["day"]*1; }
	      
  if (isset($_POST['year'])) { $ryear=$_POST["year"]*1; }
  if (isset($_POST['month'])) { $rmonth=$_POST["month"]*1; }
  if (isset($_POST['day'])) { $rday=$_POST["day"]*1; }
		    
  if (!isset($ryear))  { $ryear=$fyear; }
  if (!isset($rmonth))  { $rmonth=$fmonth; }
  if (!isset($rday))  { $rday=$fday; }
			  
  list($login)=mysql_fetch_array(mysql_query("SELECT Login FROM User_list WHERE User_list.id=$id"));
?>
		    
  <div id="cont"> <table cellspacing="0" cellpadding="0"><tr><td>
  <b>
<?  echo userinfo1;
    print " <a href=../users/edituser.php?id=$id>".$login."</a>";
    print " </b> | ";
    print "<a href=useryear.php?id=$id>"; echo userinfo5; print "</a> | ";
    print "<a href=userinfo.php?id=$id>"; echo userinfo4; print "</a> | ";
    print "<a href=userday.php?id=$id>"; echo userinfo6; print "</a>";
?>
  <br><br>
		      
  <table class="data" width="650" cellspacing="1" cellpadding="4">
  <tr align="center">
  <td class="data" ><b><? echo userinfo2; ?></b></td>
  <td class="data" ><b><? echo userinfo10; ?></b></td>
  <td class="data" ><b><? echo userinfo7; ?></b></td>
  <td class="data" ><b><? echo userinfo8; ?></b></td>
  </tr>
							    