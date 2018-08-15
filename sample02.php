<?php 
$input = '\$_(GET|POST|REQUEST)'; //ユーザ入力パターン
$safe_func = "(htmlspecialchars)";
$sink_func = "(echo|print)"; //最終到達点パターン
$filename = $argv[1];
$code = file_get_contents($filename);
// 改行区切りで意味がまとまっている前提
$lines = explode("\n", $code);
foreach ($lines as $line) {
    if (preg_match('/'.$sink_func.'.*'.$input.'/', $line)
        && !preg_match('/'.$sink_func.'.*'.$safe_func.'.*'.$input.'/', $line)
    ) {
        echo "Vulnerable: ".$line."\n";
    }
}
?>