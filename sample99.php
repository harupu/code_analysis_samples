<?php
/**
 * 正規表現ベースの簡易ソースコード検査ツール
 * 
 * @author harupu <harupu@gmail.com>
 */ 
$input = ['\\$_GET','\\$_POST','\\$_REQUEST','php:\\/\\/input'];
$safe_func  = "(mysql_real_escape_string|htmlspecialchars|urlencode|basename)";
$sink_func  = "(mysql_query|file_get_contents|readfile)"; //最終到達点パターン

//検索するフォルダ
$dir = dirname(__FILE__) . '\\vuln_codes\\';

$file_list = List_files($dir);
foreach ($file_list as $filename) {
    $code = file_get_contents($filename);
    // 改行区切りで意味がまとまっている前提
    $lines = explode("\n", $code);
    $vuln_values = [];//脆弱な入力一覧
    foreach ($input as $value) {
        //[パラメータ名、オリジナル]の配列追加
        array_push($vuln_values, [$value,$value]);
    }
    foreach ($lines as $line) {
        $assing_flg = false;
        foreach ($vuln_values as $vuln_value) {
            //代入処理
            if (preg_match('/^\s*(\$[^=]+?)\s*=.*'.$vuln_value[0].'/', $line, $match)) {
                $assing_flg = true;
                $matched_value = str_replace('/', '\\/', preg_quote($match[1]));
                //既存のリストから削除
                for ($i=0; $i<count($vuln_values); $i++) {
                    if ($vuln_values[$i][0] == $matched_value) {
                        array_splice($vuln_values, $i, 1);
                        break;
                    }
                }
                //safe_funcにかからなければ追加
                if (preg_match('/^\s*(\$[^=]+?)\s*=.*'.$vuln_value[0].'/', $line, $match)
                    && !preg_match('/^\s*(\$[^=]+?)\s*=.*'.$safe_func.'.*'.$vuln_value[0].'/', $line)
                ) {
                    array_push($vuln_values, [$matched_value,$vuln_value[1]]);
                }
            }
            //脆弱判定処理
            if (preg_match('/'.$sink_func.'.*'.$vuln_value[0].'/', $line)
                && !preg_match('/'.$sink_func.'.*'.$safe_func.'.*'.$vuln_value[0].'/', $line)
            ) {
                echo "Vulnerability in ".$filename.":\n ".$line.
                    "\n (".$vuln_value[0]." => ".$vuln_value[1].")\n";
            }
        }
        if (!$assing_flg) {
            if (preg_match('/^\s*(\$[^=]+?)\s*=.*'.$vuln_value[0].'/', $line, $match)) {
                $matched_value = str_replace('/', '\\/', preg_quote($match[1]));
                //既存のリストから削除
                for ($i=0; $i<count($vuln_values); $i++) {
                    if ($vuln_values[$i][0] == $matched_value) {
                        array_splice($vuln_values, $i, 1);
                        break;
                    }
                }
            }
        }
    }
}

/**
 * 指定されたディレクトリ配下のファイル名一覧を返す
 * 
 * @param string $dir ディレクトリ名
 * 
 * @return array ファイル名のリスト
 */
function List_files($dir)
{
    $list = array();
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        } elseif (is_file($dir . $file)) {
            $list[] = $dir . $file;
        } elseif (is_dir($dir . $file)) {
            //$list[] = $dir;
            $list = array_merge($list, list_files($dir . $file . "/"));
        }
    }
    return $list;
}
?>