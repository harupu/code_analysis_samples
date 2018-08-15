<?php 
$input = ['\\$_GET','\\$_POST','\\$_REQUEST'];
$safe_func = "(mysql_real_escape_string)";
$sink_func = "(mysql_query)"; //最終到達点パターン
$filename = $argv[1];
$code = file_get_contents($filename);
// 改行区切りで意味がまとまっている前提
$lines = explode("\n", $code);
$vuln_params = [];//脆弱な入力一覧
foreach ($input as $param) {
    //[パラメータ名、オリジナル]の配列追加
    array_push($vuln_params, [$param,$param]);
}
foreach ($lines as $line) {
    foreach ($vuln_params as $vuln_param) {
        //代入処理
        if (preg_match('/^\s*(\$[^=]+?)\s*=.*'.$vuln_param[0].'/', $line, $match)
            && !preg_match('/^\s*(\$[^=]+?)\s*=.*'.$safe_func.'.*'.$vuln_param[0].'/', $line)
        ) {
            //inputが代入された変数も脆弱な入力に追加
            array_push($vuln_params, [str_replace('$', '\\$', $match[1]),$vuln_param[0]]);
        }
        //脆弱判定処理
        if (preg_match('/'.$sink_func.'.*'.$vuln_param[0].'/', $line)
            && !preg_match('/'.$sink_func.'.*'.$safe_func.'.*'.$vuln_param[0].'/', $line)
        ) {
            echo "Vulnerable: ".$line." (".$vuln_param[0]." => ".$vuln_param[1].")\n";
        }
    }
}
?>