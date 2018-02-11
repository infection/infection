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
        $code = <<<'PHP'
<?php

class Test
{
    private $var = 3;
    public function foo(int $param, $test = 2.0): bool
    {
        return count([]) === 1;
    }
}
PHP;
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
            [Node\Stmt\Return_::class, false],
            [Node\Stmt\Property::class, false], // private $var = 3;
        ];
    }

    /**
     * @dataProvider isInsideFunctionFlagProvider
     */
    public function test_it_sets_is_inside_function(string $nodeClass, bool $expectedResult)
    {
        $code = <<<'PHP'
<?php

class Test
{
    private $var = 3;
    public function foo(int $param, $test = 2.0): bool
    {
        return count([]) === 1;
    }
}
PHP;
        $statements = $this->parser->parse($code);

        $traverser = new NodeTraverser();
        $spyVisitor = $this->getSpyVisitor($nodeClass);

        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new WrappedFunctionInfoCollectorVisitor());
        $traverser->addVisitor($spyVisitor);

        $traverser->traverse($statements);

        $this->assertSame($expectedResult, $spyVisitor->isInsideFunction());
    }

    public function isInsideFunctionFlagProvider()
    {
        return [
            [Node\Stmt\ClassMethod::class, false],
            [Node\Param::class, true], // $param
            [Node\Scalar\DNumber::class, true], // 2.0
            [Node\Scalar\LNumber::class, true], // 1
            [Node\Expr\BinaryOp\Identical::class, true], // ===
            [Node\Arg::class, true],
            [Node\Expr\Array_::class, true], // []
            [Node\Stmt\Class_::class, false], // class Test
            [Node\Stmt\Return_::class, true],
            [Node\Stmt\Property::class, false], // private $var = 3;
        ];
    }

    private function getSpyVisitor(string $nodeClass): NodeVisitorAbstract
    {
        return new class($nodeClass) extends NodeVisitorAbstract {
            /** @var string */
            private $nodeClassUnderTest;

            private $isPartOfSignature;

            private $isInsideFunction;

            public function __construct(string $nodeClass)
            {
                $this->nodeClassUnderTest = $nodeClass;
            }

            public function leaveNode(Node $node)
            {
                if ($node instanceof $this->nodeClassUnderTest) {
                    $this->isPartOfSignature = $node->getAttribute(WrappedFunctionInfoCollectorVisitor::IS_ON_FUNCTION_SIGNATURE, false);
                }

                if ($node instanceof $this->nodeClassUnderTest) {
                    $this->isInsideFunction = $node->getAttribute(WrappedFunctionInfoCollectorVisitor::IS_INSIDE_FUNCTION_KEY, false);
                }
            }

            public function isPartOfSignature()
            {
                return $this->isPartOfSignature;
            }

            public function isInsideFunction()
            {
                return $this->isInsideFunction;
            }
        };
    }

    public function test_it_sets_function_scope_key()
    {
        $code = <<<'PHP'
<?php

class Test
{
    private $var = 3;
    public function foo(int $param, $test = 2.0)
    {
        return count([]) === 1;
    }
    private function hello(): string
    {
        return 'hello';
    }
    private function bye(): ?string
    {
        return 'bye';
    }
}
PHP;

        $statements = $this->parser->parse($code);

        $traverser = new NodeTraverser();
        $spyVisitor = $this->getFunctionScopeSpyVisitor();

        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new WrappedFunctionInfoCollectorVisitor());
        $traverser->addVisitor($spyVisitor);

        $traverser->traverse($statements);
        $scopes = $spyVisitor->getFunctionScope();

        foreach ($scopes[6] as $scope) {
            $this->assertNull($scope->getReturnType());
        }

        foreach ($scopes[8] as $scope) {
            $this->assertNull($scope->getReturnType());
        }

        foreach ($scopes[12] as $scope) {
            $this->assertSame('string', $scope->getReturnType());
        }

        foreach ($scopes[14] as $scope) {
            $this->assertInstanceOf(Node\NullableType::class, $scope->getReturnType());
            $this->assertSame('string', $scope->getReturnType()->type);
        }

        foreach ($scopes[16] as $scope) {
            $this->assertInstanceOf(Node\NullableType::class, $scope->getReturnType());
            $this->assertSame('string', $scope->getReturnType()->type);
        }

        //Only these 5 lines have FUNCTION_SCOPE_KEY set
        $this->assertCount(5, $scopes);
    }

    private function getFunctionScopeSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            private $functionScope = [];

            public function leaveNode(Node $node)
            {
                if ($scope = $node->getAttribute(WrappedFunctionInfoCollectorVisitor::FUNCTION_SCOPE_KEY, false)) {
                    $this->functionScope[$node->getLine()][$node->getType()] = $scope;
                }
            }

            public function getFunctionScope()
            {
                return $this->functionScope;
            }
        };
    }
}
