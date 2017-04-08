<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter;
use Infection\Visitor\MutatorVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\InsideFunctionDetectorVisitor;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\ReturnValue\FunctionCall;

$lexer = new PhpParser\Lexer(array(
    'usedAttributes' => array(
        'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos'
    )
));
$parser = (new PhpParser\ParserFactory)->create(PhpParser\ParserFactory::PREFER_PHP7, $lexer);
$traverser     = new NodeTraverser;
$prettyPrinter = new PrettyPrinter\Standard;
$nodeDumper = new PhpParser\NodeDumper;

$originalCode = '<?php 
array(1) + array();
[1] + [];

13 +  15;

function test() {
    1 + 0;
    
    return count([]);
}

class A {
    public function add($a, $b)
    {
        return $a + $b - 1;
    }
}

$callback = function () {
    3 + 4;
    -1 - 2;
};
';

$mutators = [
    new Plus(),
    new Minus(),
    new FunctionCall()
];

$mutationsCollectorVisitor = new MutationsCollectorVisitor($mutators);

$traverser->addVisitor(new ParentConnectorVisitor());
$traverser->addVisitor(new InsideFunctionDetectorVisitor());
$traverser->addVisitor($mutationsCollectorVisitor);

$initialStatements = $parser->parse($originalCode);
// traverse
$stmts = $traverser->traverse($initialStatements);
// $stmts is an array of statement nodes
//var_dump($stmts);
echo $nodeDumper->dump($stmts), "\n";

// pretty print
$code = $prettyPrinter->prettyPrintFile($stmts);

echo "\nOriginal Code:\n";
echo $code . "\n";
echo "\n\n";

$traverser = new NodeTraverser();

foreach ($mutationsCollectorVisitor->getMutations() as $index => $mutation) {
//    $code = file_get_contents($phpFileName);

    $visitor = new MutatorVisitor($mutation);

    $traverser->addVisitor($visitor);

    $originalStatements = $parser->parse($originalCode);
    $mutatedStatements = $traverser->traverse($originalStatements);

    $mutatedCode = $prettyPrinter->prettyPrintFile($mutatedStatements);
    echo "\nMutation #{$index}\n";
    echo $mutatedCode . "\n";

//    file_put_contents('path', $mutatedCode);

    $traverser->removeVisitor($visitor);
}
