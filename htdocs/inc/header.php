<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Панель администратора</title>
	<link rel="stylesheet" type="text/css" href=<? echo "\"/$style.css\""; ?>>
	<meta http-equiv="content-type" content="text/html; charset=cp-1251">
	
<script name="javascript">
function checkAll(check) {
var boxes = document.def.elements.length;
if(check) {
	for(i=0; i<boxes; i++) {
		document.def.elements[i].checked = true;
	}
} else {
	for(i=0; i<boxes; i++) {
		document.def.elements[i].checked = false;
	}
}
}
</script>	

</head>
<body>
<div id="title">Stat for DP8</div>

<div id="navi">
	<a href="/admin/reports"><? echo menu1; ?></a> | 
	<a href="/admin/groups"><? echo menu2; ?></a> |
	<a href="/admin/users"><? echo menu3; ?></a> |
	<a href="/admin/filters"><? echo menu4; ?> </a> |
	<a href="/admin/wan"> Wan </a> | 
	<a href="/admin/nets"> Сети </a> |
	<a href="/admin/customers"> Customers </a>
</div>


