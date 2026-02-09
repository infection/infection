<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
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

namespace Infection\Tests\PhpParser\Visitor\VisitorCollectorIntegration;

use Infection\Testing\SingletonContainer;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase;
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\file_get_contents;

/**
 * The goal of this test is to showcase the state of the tree as the MutationVisitor sees it.
 */
#[Group('integration')]
#[CoversNothing]
final class VisitorCollectorIntegrationTest extends VisitorTestCase
{
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    #[DataProvider('nodeProvider')]
    public function test_it_creates_a_rich_ast(
        string $code,
        string $expected,
    ): void {
        $traverserFactory = SingletonContainer::getContainer()->getNodeTraverserFactory();

        $nodes = $this->parse($code);

        $this->addIdsToNodes($nodes);
        $traverserFactory->createPreTraverser()->traverse($nodes);
        $traversedNodes = $traverserFactory
            ->create(
                new MarkTraversedNodesAsVisitedVisitor(),
            )
        ->traverse($nodes);

        $actual = $this->dumper->dump($traversedNodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield [
            file_get_contents(self::FIXTURES_DIR . '/TwoAdditions.php'),
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                )
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                            )
                        )
                        nodeId: 0
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 7
                                    origNode: nodeId(7)
                                    parent: nodeId(6)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 9
                                            origNode: nodeId(9)
                                            parent: nodeId(8)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(8)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: first
                                        )
                                        returnType: Identifier(
                                            nodeId: 10
                                            origNode: nodeId(10)
                                            parent: nodeId(8)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(8)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: first
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Plus(
                                                    left: Scalar_Int(
                                                        rawValue: 1
                                                        kind: KIND_DEC (10)
                                                        nodeId: 13
                                                        origNode: nodeId(13)
                                                        parent: nodeId(12)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(8)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: first
                                                    )
                                                    right: Scalar_Int(
                                                        rawValue: 2
                                                        kind: KIND_DEC (10)
                                                        nodeId: 14
                                                        origNode: nodeId(14)
                                                        parent: nodeId(12)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(8)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: first
                                                    )
                                                    nodeId: 12
                                                    origNode: nodeId(12)
                                                    parent: nodeId(11)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(8)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: first
                                                )
                                                nodeId: 11
                                                origNode: nodeId(11)
                                                parent: nodeId(8)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(8)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: first
                                            )
                                        )
                                        nodeId: 8
                                        origNode: nodeId(8)
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: first
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 16
                                            origNode: nodeId(16)
                                            parent: nodeId(15)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(15)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: second
                                        )
                                        returnType: Identifier(
                                            nodeId: 17
                                            origNode: nodeId(17)
                                            parent: nodeId(15)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(15)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: second
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Minus(
                                                    left: Scalar_Int(
                                                        rawValue: 1
                                                        kind: KIND_DEC (10)
                                                        nodeId: 20
                                                        origNode: nodeId(20)
                                                        parent: nodeId(19)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(15)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: second
                                                    )
                                                    right: Scalar_Int(
                                                        rawValue: 2
                                                        kind: KIND_DEC (10)
                                                        nodeId: 21
                                                        origNode: nodeId(21)
                                                        parent: nodeId(19)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(15)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: second
                                                    )
                                                    nodeId: 19
                                                    origNode: nodeId(19)
                                                    parent: nodeId(18)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(15)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: second
                                                )
                                                nodeId: 18
                                                origNode: nodeId(18)
                                                parent: nodeId(15)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(15)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: second
                                            )
                                        )
                                        nodeId: 15
                                        origNode: nodeId(15)
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: second
                                    )
                                )
                                nodeId: 6
                                origNode: nodeId(6)
                                parent: nodeId(4)
                            )
                        )
                        kind: 1
                        nodeId: 4
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];
    }
}
