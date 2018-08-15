<?php
echo $_GET["test"]; //vulnerable
echo htmlspecialchars($_GET["test"]); //secure
?>
<a href="?test=test">click!</a>