<?php 
require_once "vendor/autoload.php";
use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

$input = '\$_(GET|POST|REQUEST)'; //ユーザ入力パターン
$sink_func = [//最終到達点パターン
    'special'=> ["print", "echo"],//特殊関数
    'normal'=> ["mysql_query","file_get_contents"]
]; 
$filename = $argv[1];
$code = file_get_contents($filename);

$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
try {
    $ast = $parser->parse($code);
} catch (Error $error) {
    echo "Parse error: {$error->getMessage()}\n";
    exit;
}

$traverser = new NodeTraverser();
$traverser->addVisitor(
    new class extends NodeVisitorAbstract {
        public function enterNode(Node $node) {
            global $sink_func;
            foreach ($sink_func as $type => $funcs ) {
                if ($type === "special") {
                    foreach ($funcs as $func ) {
                        if ($func === "echo" && $node instanceof Node\Stmt\Echo_) {
                            checkInput("echo", prettyPrint($node->exprs));
                        } elseif ($func === "print" && $node instanceof Node\Expr\Print_) {
                            checkInput("print", prettyPrint($node->expr));
                        }
                    }
                } elseif ($node instanceof Node\Expr\FuncCall) {
                    foreach ($funcs as $func ) {
                        if ($node->name->parts[0] === $func) {
                            checkInput($func, prettyPrint($node->args));
                        }
                    }
                }
            }
        }
    }
);

$ast = $traverser->traverse($ast);

function prettyPrint($obj)
{
    $prettyPrinter = new PrettyPrinter\Standard;
    $tmp_obj = is_array($obj)?$obj:array($obj);
    return str_replace(
        "\n", ", ", preg_replace(
            '/^<\?php[\r\n]+/', "", $prettyPrinter->prettyPrintFile($tmp_obj)
        )
    );
}

function checkInput($sink, $stmt_str)
{
    //オブジェクトのチェックは複雑になるため省略
    global $input;
    if (preg_match('/'.$input.'/', $stmt_str)) {
        echo "Vulnerable: ".$sink.": ".$stmt_str."\n";
    }    
}


?>