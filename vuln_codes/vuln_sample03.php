<?php
$test = $_GET["test"];
echo $test; // vulnerable
?>
<a href="?test=test">click!</a>