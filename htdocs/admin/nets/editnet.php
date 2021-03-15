<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");

	$id=$_GET["id"]*1;
	if (!$_POST["id"] && !$id) {
		header("location: index.php");
	}
	
	if ($_POST["editnet"]) {
		$id=$_POST["id"]*1;
		$fname=mysql_escape_string(substr($_POST["fname"],0,30));
		$fexpr=mysql_escape_string(substr($_POST["fexpr"],0,40));
		$ffree=$_POST["ffree"]*1;
		$flog=$_POST["flog"];
		if ($ffree==0) { $flog = 1; }
		$fdomain=$_POST["fdomain"]*1;
		mysql_query("update nets set name='$fname',expression='$fexpr',free='$ffree',log='$flog',domain='$fdomain' where id='$id'");
		header("location: index.php");
		}
	
	unset($_POST); 

        $filters=mysql_query("SELECT U.name, U.expression, U.free, U.log, U.domain from nets U where id=$id");
	list($fname,$fexpr,$ffree,$flog,$fdomain)=mysql_fetch_array($filters);
			
	require_once("../../inc/header.php");
?>
	<div id="cont">

	<form name="def" action="editnet.php?id=<? echo $id; ?>" method="post">
	<input type="hidden" name="id" value=<? echo $id; ?>>
        <table class="data" cellspacing="0" cellpadding="4">
        <tr>
        <td ><b>Название</b></td>
        <td ><b>Сеть</b></td>
        <td ><b>Бесплатно</b></td>
        <td ><b>Лог</b></td>
        <td ><b>Домен</b></td>
       	</tr>
	<td align=left><input type="text" name="fname" value="<? echo $fname; ?>" size=20></td>
	<td ><input type="text" name="fexpr" value="<? echo $fexpr; ?>" size=40></td>
	<td ><input type="text" name="ffree" value="<? echo $ffree; ?>" size=10></td>
	<td ><input type="text" name="fdstport" value="<? echo $flog; ?>" size=10></td>
	<td ><input type="text" name="fdomain" value="<? echo $fdomain; ?>" size=10></td><tr>
	<tr>
	<td colspan=2><input type="submit" name="editnet" value="Сохранить"></td>
	</tr>
	</table>
	</form>
<?
	require_once("../../inc/footer.php");
?>