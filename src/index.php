<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpParser\Error;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use Infection\Visitor\MutatorNodeVisitor;
use Infection\Visitor\MutationsCollectorNodeVisitor;
use Infection\Mutator\Arithmetic\Plus;

$lexer = new PhpParser\Lexer(array(
    'usedAttributes' => array(
        'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos'
    )
));
$parser = (new PhpParser\ParserFactory)->create(PhpParser\ParserFactory::PREFER_PHP7, $lexer);
$traverser     = new NodeTraverser;
$prettyPrinter = new PrettyPrinter\Standard;
$nodeDumper = new PhpParser\NodeDumper;

$code = '<?php 
[1] + [];

1 + 0;
13 +  15;

function test() {
    1 + 0;
}';

$mutators = [
    new Plus(),
];
$mutationsCollectorVisitor = new MutationsCollectorNodeVisitor($mutators);

$traverser->addVisitor($mutationsCollectorVisitor);

$initialStatements = $parser->parse($code);
// traverse
$stmts = $traverser->traverse($initialStatements);
// $stmts is an array of statement nodes
//var_dump($stmts);
echo $nodeDumper->dump($stmts), "\n";

// pretty print
$code = $prettyPrinter->prettyPrintFile($stmts);

echo $code . "\n";

//var_dump($mutationsCollectorVisitor->getMutations());
foreach($mutationsCollectorVisitor->getMutations() as $mutation) {
//    $code = file_get_contents($phpFileName);

//    $stmts = $parser->parse($code);

    $visitor = new MutatorNodeVisitor($mutation);

    $traverser->addVisitor($visitor);

    $mutatedStatements = $traverser->traverse($initialStatements);

    $mutatedCode = $prettyPrinter->prettyPrintFile($mutatedStatements);

    echo $mutatedCode . "\n";

//    file_put_contents('path', $mutatedCode);

    $traverser->removeVisitor($visitor);
}
