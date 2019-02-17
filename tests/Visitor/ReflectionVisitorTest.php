<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Visitor;

use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/**
 * @internal
 */
final class ReflectionVisitorTest extends AbstractBaseVisitorTest
{
    private $spyVisitor;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var string
     */
    private $code;

    protected function setUp(): void
    {
        $this->spyVisitor = $this->getInsideFunctionSpyVisitor();

        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
            ],
        ]);

        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
        $this->code = $this->getFileContent('Reflection/rv-part-of-signature-flag.php');
    }

    /**
     * @dataProvider isPartOfSignatureFlagProvider
     */
    public function test_it_sets_is_part_of_signature_flag(string $nodeClass, bool $expectedResult): void
    {
        $statements = $this->parser->parse($this->code);

        $traverser = new NodeTraverser();
        $spyVisitor = $this->getPartOfSignatureSpyVisitor($nodeClass);

        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new FullyQualifiedClassNameVisitor());
        $traverser->addVisitor(new ReflectionVisitor());
        $traverser->addVisitor($spyVisitor);

        $traverser->traverse($statements);

        $this->assertSame($expectedResult, $spyVisitor->isPartOfSignature());
    }

    public function test_it_detects_if_traversed_inside_class_method(): void
    {
        $code = $this->getFileContent('Reflection/rv-inside-class-method.php');

        $this->parseAndTraverse($code);

        $this->assertTrue($this->spyVisitor->isInsideFunction);
    }

    public function test_it_does_not_travers_global_plain_function(): void
    {
        $code = $this->getFileContent('Reflection/rv-inside-function.php');

        $this->parseAndTraverse($code);

        $this->assertFalse($this->spyVisitor->isInsideFunction);
    }

    public function test_travers_plain_function_inside_a_class(): void
    {
        $code = $this->getFileContent('Reflection/rv-inside-plain-function-in-class.php');
        $spyVisitor = $this->getSpyVisitor(Node\Expr\FuncCall::class);

        $this->parseAndTraverse($code, $spyVisitor);

        $this->assertTrue($spyVisitor->spyCalled);
    }

    public function test_travers_plain_function_inside_a_closure(): void
    {
        $code = $this->getFileContent('Reflection/rv-inside-plain-function-in-closure.php');
        $spyVisitor = $this->getSpyVisitor(Node\Expr\FuncCall::class);

        $this->parseAndTraverse($code, $spyVisitor);

        $this->assertTrue($spyVisitor->spyCalled);
    }

    public function test_it_detects_if_traversed_inside_closure(): void
    {
        $code = $this->getFileContent('Reflection/rv-inside-closure.php');

        $this->parseAndTraverse($code);

        $this->assertTrue($this->spyVisitor->isInsideFunction);
    }

    public function test_it_does_not_add_inside_function_flag_if_not_needed(): void
    {
        $code = $this->getFileContent('Reflection/rv-without-function.php');

        $this->parseAndTraverse($code);

        $this->assertFalse($this->spyVisitor->isInsideFunction);
    }

    public function test_it_correctly_works_with_anonymous_classes(): void
    {
        $code = $this->getFileContent('Reflection/rv-anonymous-class.php');

        $this->parseAndTraverse($code);

        $this->assertTrue($this->spyVisitor->isInsideFunction);
    }

    public function test_it_sets_reflection_class_to_nodes(): void
    {
        $code = $this->getFileContent('Reflection/rv-inside-class-method.php');
        $reflectionSpyVisitor = $this->getReflectionClassSpyVisitor();

        $this->parseAndTraverse($code, $reflectionSpyVisitor);

        $this->assertInstanceOf(\ReflectionClass::class, $reflectionSpyVisitor->reflectionClass);
        $this->assertSame(\InfectionReflectionClassMethod\Foo::class, $reflectionSpyVisitor->reflectionClass->getName());
    }

    public function test_it_sets_reflection_class_to_nodes_in_anonymous_class(): void
    {
        $code = $this->getFileContent('Reflection/rv-anonymous-class-inside-class.php');
        $reflectionSpyVisitor = $this->getReflectionClassesSpyVisitor();

        $this->parseAndTraverse($code, $reflectionSpyVisitor);

        $this->assertNull($reflectionSpyVisitor->fooReflectionClass);

        $this->assertInstanceOf(\ReflectionClass::class, $reflectionSpyVisitor->createAnonymousClassReflectionClass);
        $this->assertSame(\InfectionReflectionAnonymousClass\Bug::class, $reflectionSpyVisitor->createAnonymousClassReflectionClass->getName());
    }

    public function isPartOfSignatureFlagProvider(): array
    {
        return [
            [Node\Stmt\ClassMethod::class, true],
            [Node\Param::class, true], // $param
            [Node\Scalar\DNumber::class, true], // 2.0
            [Node\Scalar\LNumber::class, false], // 1
            [Node\Expr\BinaryOp\Identical::class, false], // ===
            [Node\Arg::class, false],
            [Node\Expr\Array_::class, false], // []
        ];
    }

    private function getPartOfSignatureSpyVisitor(string $nodeClass)
    {
        return new class($nodeClass) extends NodeVisitorAbstract {
            /** @var string */
            private $nodeClassUnderTest;

            private $isPartOfSignature;

            public function __construct(string $nodeClass)
            {
                $this->nodeClassUnderTest = $nodeClass;
            }

            public function leaveNode(Node $node): void
            {
                if ($node instanceof $this->nodeClassUnderTest) {
                    $this->isPartOfSignature = $node->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);
                }
            }

            public function isPartOfSignature()
            {
                return $this->isPartOfSignature;
            }
        };
    }

    private function getSpyVisitor(string $nodeClass)
    {
        return new class($nodeClass) extends NodeVisitorAbstract {
            /** @var string */
            private $nodeClassUnderTest;

            public $spyCalled = false;

            public function __construct(string $nodeClass)
            {
                $this->nodeClassUnderTest = $nodeClass;
            }

            public function leaveNode(Node $node): void
            {
                if ($node instanceof $this->nodeClassUnderTest) {
                    $this->spyCalled = true;
                }
            }
        };
    }

    private function getInsideFunctionSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            public $isInsideFunction = false;

            public function enterNode(Node $node)
            {
                if ($node->hasAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY)) {
                    $this->isInsideFunction = true;

                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }
        };
    }

    private function getReflectionClassSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            /** @var \ReflectionClass */
            public $reflectionClass;

            public function enterNode(Node $node)
            {
                if ($node->hasAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY)) {
                    $this->reflectionClass = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }
        };
    }

    private function getReflectionClassesSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            public $fooReflectionClass;
            public $createAnonymousClassReflectionClass;

            public function enterNode(Node $node)
            {
                $name = $node->getAttribute(ReflectionVisitor::FUNCTION_NAME);

                if ($name === 'foo') {
                    $this->fooReflectionClass = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }

            public function leaveNode(Node $node): void
            {
                $name = $node->getAttribute(ReflectionVisitor::FUNCTION_NAME);

                if ($name === 'createAnonymousClass') {
                    $this->createAnonymousClassReflectionClass = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);
                }
            }
        };
    }

    private function parseAndTraverse(string $code, NodeVisitor $nodeVisitor = null): void
    {
        $nodes = $this->getNodes($code);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new FullyQualifiedClassNameVisitor());
        $traverser->addVisitor(new ReflectionVisitor());
        $traverser->addVisitor($nodeVisitor ?: $this->spyVisitor);

        $traverser->traverse($nodes);
    }
}
