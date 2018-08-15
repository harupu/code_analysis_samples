<?php
$root = "/document_root/";
$param1 = $_GET["param1"];
$param2 = $_GET["param2"];
$content1 = file_get_contents($root.$param1);
$content2 = file_get_contents($root.$param2.".txt");
?>
