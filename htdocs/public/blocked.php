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

    list($rhour,$rday,$rmonth,$ryear)=explode(" ",date("H j n Y",time()));
  
    if ($ip=="unknown") { print "Error detecting user!!!"; }

    list($id,$proxy)=mysql_fetch_array(mysql_query("SELECT userid,proxy FROM User_auth WHERE IP='$ip' and deleted=0"));
?>
    <div id="cont"> 
    <table cellspacing="0" cellpadding="0"><tr><td>
    <table class="data" width="650" cellspacing="1" cellpadding="4">
    <td align="center">
<?
    if ($id>0) {
	if (($blocked) or ($enabled=0)) {
	    if ($blocked=1) {
            print "<tr class='data'><div id='msg'><b>заблокирован по трафику!</div></b></tr><br>\n";
            }
	    elseif ($enabled=0) {
            print "<tr class='data'><div id='msg'><b>Доступ запрещён администратором!</div></b></tr><br>\n";
            }
	}
	elseif ($proxy=0) {
        print "<tr class='data'><div id='msg'><b>Пользователю $login работа через прокси-сервер не разрешена!</div></b></tr><br>\n";
	}

    list($login,$limit,$limit1,$limit2,$blocked,$enabled)=mysql_fetch_array(mysql_query("SELECT Login,permonth,perday,perhour,blocked,enabled FROM User_list WHERE User_list.id=$id"));
    $limit=$limit*$KB*$KB;
    $limit1=$limit1*$KB*$KB;
    $limit2=$limit2*$KB*$KB;
    		    
    $useritog=mysql_query("SELECT SUM(tin),SUM(tout) FROM (select userid,SUM(bytein) as tin,
                SUM(byteout) as tout from User_stats
                where ((YEAR(`Dat`)=$ryear) and (MONTH(`Dat`)=$rmonth))
                GROUP by userid) as V, User_auth, User_list
                WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.id=$id)
                GROUP by Login Order by Login");
          
    list($uin,$uout)=mysql_fetch_array($useritog);
    print "<tr class='data'><div id='msg'><b>Пользователь: $login Адрес: $ip</div></b></tr><br>\n";
    print "<tr class='data'><div id='msg2'>Текущий трафик</div></tr>\n";
    print "<tr class='data'><div id='msg2'>за месяц ". fbytes($uin) ." - лимит ". fbytes($limit) ."</div></tr>\n";

    $useritog=mysql_query("SELECT SUM(tin),SUM(tout) FROM (select userid,SUM(bytein) as tin,
                SUM(byteout) as tout from User_stats
                where ((YEAR(`Dat`)=$ryear) and (MONTH(`Dat`)=$rmonth) and (DAY(`Dat`)=$rday))
                GROUP by userid) as V, User_auth, User_list
                WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.id=$id)
                GROUP by Login Order by Login");
          
    list($uin,$uout)=mysql_fetch_array($useritog);
    print "<tr class='data'><div id='msg2'>за день ". fbytes($uin) ." - лимит ". fbytes($limit1) ."</div></tr>\n";

    $useritog=mysql_query("SELECT SUM(tin),SUM(tout) FROM (select userid,SUM(bytein) as tin,
                SUM(byteout) as tout from User_stats
                where ((YEAR(`Dat`)=$ryear) and (MONTH(`Dat`)=$rmonth) and (DAY(`Dat`)=$rday) and (HOUR(`Dat`)=$rhour))
                GROUP by userid) as V, User_auth, User_list
                WHERE (V.userid=User_auth.id) and (User_auth.userid=User_list.id) and (User_list.id=$id)
                GROUP by Login Order by Login");
          
    list($uin,$uout)=mysql_fetch_array($useritog);
    print "<tr class='data'><div id='msg2'>за час ". fbytes($uin) ." - лимит ". fbytes($limit2) ."</div></tr>\n";

    print "<tr class='data'><a href=/public/index.php><div id='msg2'>Статистика</div></a></tr>\n";
    print "<tr class='data'><br></tr>\n";
    print "</td>\n";
    }
    else {
        print "<tr class='data'><div id='msg'><b>Client $ip not found!!!</div></b></tr><br>\n";
    }    

  require_once("../inc/footer.php");
?>