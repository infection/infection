<?php

require 'vendor/autoload.php';


use PhpParser\Error;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

// $code = '<?php 1 + 2;
// function test() {
//     return 4;
// }
// function outer() {
//   return inner();
// }
// function inner() {
//   return "inner";
// }
// ';
$code = '
<?php
function isPositiveDelta($delta): bool
{
    if ($delta === 0) {
        return true;
    }

    return $delta < 0;
}';
$code = '<?php 1 < 0;';

// $code = '<?php [2] + [1];';
$lexer = new PhpParser\Lexer(array(
    'usedAttributes' => array(
        'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos'
    )
));
$parser = (new PhpParser\ParserFactory)->create(PhpParser\ParserFactory::PREFER_PHP7, $lexer);
$traverser     = new NodeTraverser;
$prettyPrinter = new PrettyPrinter\Standard;
$nodeDumper = new PhpParser\NodeDumper;

class ReturnIntegerMutatorVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
        if ($node instanceof Node\Stmt\Return_) {
            if ($node->expr instanceof Node\Scalar\LNumber) {
                return new Node\Stmt\Return_(
                    new Node\Scalar\LNumber($node->expr->value * -1, $node->getAttributes())
                );
            }
        }
    }
}

class ReturnFunctionCallMutatorVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
        if ($node instanceof Node\Stmt\Return_) {
            if ($node->expr instanceof Node\Expr\FuncCall) {
                return [
                    $node->expr,
                    new Node\Stmt\Return_(
                        new Node\Expr\ConstFetch(new Node\Name('null'))
                    )
                ];
            }
        }
    }
}

class SmartMutatorVisitor extends NodeVisitorAbstract
{
    private $tokens;

    public function setTokens(array $tokens) {
        $this->tokens = $tokens;
    }

    public function leaveNode(Node $node) {
        if ($node instanceof Node\Expr\BinaryOp\Smaller) {
            $startTokenPos = $node->getAttribute('startTokenPos');
            $endTokenPos = $node->getAttribute('endTokenPos');

            $startFilePos = $node->getAttribute('startFilePos');
            $endFilePos = $node->getAttribute('endFilePos');

            var_dump(sprintf('startTokenPos=%s, token=%s', $startTokenPos, var_export($this->tokens[$startTokenPos], true)));
            var_dump(sprintf('endTokenPos=%s, token=%s', $endTokenPos, var_export($this->tokens[$endTokenPos], true)));

            // var_dump(sprintf('startFilePos=%s, token=%s', $startFilePos, var_export($this->tokens[$startFilePos], true)));
            // var_dump(sprintf('endFilePos=%s, token=%s', $endFilePos, var_export($this->tokens[$endFilePos], true)));
            var_dump('!!!!!!!!!!!');
            var_dump($node);
            var_dump($this->tokens);
            die;
        }
    }
}

$smartMutatorVisitor = new SmartMutatorVisitor();

// add your visitor
//$traverser->addVisitor(new MyNodeVisitor);
//$traverser->addVisitor(new ReturnIntegerMutatorVisitor);
//$traverser->addVisitor(new ReturnFunctionCallMutatorVisitor);
$traverser->addVisitor($smartMutatorVisitor);

try {
    $stmts = $parser->parse($code);
    $smartMutatorVisitor->setTokens($lexer->getTokens());
    // traverse
    $stmts = $traverser->traverse($stmts);
    // $stmts is an array of statement nodes
    var_dump($stmts);
    echo $nodeDumper->dump($stmts), "\n";

    // pretty print
    $code = $prettyPrinter->prettyPrintFile($stmts);

    echo $code;
} catch (Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}
