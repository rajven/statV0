<?php
	define("CONFIG",1);
	define("SQL",1);
	require_once("config.php");
	require_once("sql.php");
	function auth() {
		header("WWW-Authenticate: Basic realm=\"Administration Panel\"");
		header("HTTP/1.0 401 Unauthorized");
		echo "You must enter a valid login and password to access this resource\n";  
		exit;
	}

    session_start();

    unset($_SESSION['user_id']);

    $login=mysql_real_escape_string(substr($_SERVER['PHP_AUTH_USER'],0,20));
    $pass=md5($_SERVER['PHP_AUTH_PW']);
    $query = "SELECT id FROM Customers WHERE Login='{$login}' AND `Pwd`='{$pass}' LIMIT 1";
    $sql = mysql_query($query) or die(mysql_error());

    if (mysql_num_rows($sql) == 1) {
        $row = mysql_fetch_assoc($sql);
        $_SESSION['user_id'] = $row['id'];
    }

    if (!isset($_SESSION['user_id'])) { auth(); }

?>