<?php

namespace Infection\Tests\Mutator;

use Infection\Mutator\Mutator;
use Infection\Tests\Fixtures\SimpleMutatorVisitor;
use PHPUnit\Framework\TestCase;
use PhpParser\Lexer;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\NodeTraverser;

abstract class AbstractMutator extends TestCase
{
    /**
     * @var Mutator
     */
    protected $mutator;

    abstract protected function getMutator() : Mutator;

    protected function setUp()
    {
        $this->mutator = $this->getMutator();
    }

    protected function getNodes($code) : array
    {
        $lexer = new Lexer();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        return $parser->parse($code);
    }

    protected function mutate($code)
    {
        $traverser = new NodeTraverser();
        $prettyPrinter = new Standard();

        $nodes = $this->getNodes($code);

        $traverser->addVisitor(new SimpleMutatorVisitor($this->mutator));

        $mutatedNodes = $traverser->traverse($nodes);

        return $prettyPrinter->prettyPrintFile($mutatedNodes);

    }
}