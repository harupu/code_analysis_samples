<?php
$param1 = $_GET["param1"];
$param2 = mysql_real_escape_string($_GET["param2"]);
$sql = "select * from xxx where param1='".$param1."' and param2='".$param2."'";
mysql_query($sql);
?>
