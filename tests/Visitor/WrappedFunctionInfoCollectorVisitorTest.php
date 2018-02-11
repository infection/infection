<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Visitor;

use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\WrappedFunctionInfoCollectorVisitor;
use Mockery;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class WrappedFunctionInfoCollectorVisitorTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var Parser
     */
    private $parser;

    protected function setUp()
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
            ],
        ]);

        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
    }

    /**
     * @dataProvider isPartOfSignatureFlagProvider
     */
    public function test_it_sets_is_part_of_signature_flag(string $nodeClass, bool $expectedResult)
    {
        $code = <<<'CODE'
<?php

class Test
{
    private $var = 3;
    public function foo(int $param, $test = 2.0): bool
    {
        return count([]) === 1;
    }
}
CODE;
        $statements = $this->parser->parse($code);

        $traverser = new NodeTraverser();
        $spyVisitor = $this->getSpyVisitor($nodeClass);

        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new WrappedFunctionInfoCollectorVisitor());
        $traverser->addVisitor($spyVisitor);

        $traverser->traverse($statements);

        $this->assertSame($expectedResult, $spyVisitor->isPartOfSignature());
    }

    public function isPartOfSignatureFlagProvider()
    {
        return [
            [Node\Stmt\ClassMethod::class, true],
            [Node\Param::class, true], // $param
            [Node\Scalar\DNumber::class, true], // 2.0
            [Node\Scalar\LNumber::class, false], // 1
            [Node\Expr\BinaryOp\Identical::class, false], // ===
            [Node\Arg::class, false],
            [Node\Expr\Array_::class, false], // []
            [Node\Stmt\Class_::class, false], // class Test
        ];
    }

    private function getSpyVisitor(string $nodeClass): NodeVisitorAbstract
    {
        return new class($nodeClass) extends NodeVisitorAbstract {
            /** @var string */
            private $nodeClassUnderTest;

            private $isPartOfSignature;

            public function __construct(string $nodeClass)
            {
                $this->nodeClassUnderTest = $nodeClass;
            }

            public function leaveNode(Node $node)
            {
                if ($node instanceof $this->nodeClassUnderTest) {
                    $this->isPartOfSignature = $node->getAttribute(WrappedFunctionInfoCollectorVisitor::IS_ON_FUNCTION_SIGNATURE, false);
                }
            }

            public function isPartOfSignature()
            {
                return $this->isPartOfSignature;
            }
        };
    }
}
