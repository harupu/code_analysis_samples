<?php
$test = $_GET["test"];
echo $test; // vulnerable
$test2 = htmlspecialchars($_GET["test"]);
echo $test2; // secure
?>
<a href="?test=test">click!</a>