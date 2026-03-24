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

namespace Infection\Tests\PhpParser\Visitor\EnrichmentTraverse;

use Infection\Differ\ChangedLinesRange;
use Infection\PhpParser\Visitor\LabelMutationCandidatesVisitor;
use Infection\Source\Matcher\NullSourceLineMatcher;
use Infection\Source\Matcher\SimpleSourceLineMatcher;
use Infection\Testing\SingletonContainer;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\file_get_contents;

/**
 * The goal of this test is to showcase the state of the tree as the MutationVisitor sees it.
 */
#[Group('integration')]
#[CoversNothing]
final class EnrichmentTraverseIntegrationTest extends VisitorTestCase
{
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    /**
     * @param list<ChangedLinesRange>|null $changedLinesRange
     */
    #[DataProvider('nodeProvider')]
    public function test_it_creates_a_rich_ast(
        string $code,
        ?array $changedLinesRange,
        string $expected,
    ): void {
        $traverserFactory = SingletonContainer::getContainer()->getNodeTraverserFactory();

        $sourceLineMatcher = $changedLinesRange === null
            ? new NullSourceLineMatcher()
            : new SimpleSourceLineMatcher($changedLinesRange);

        $nodes = $this->parse($code);

        $this->addIdsToNodes($nodes);
        $traverserFactory->createEnrichmentTraverser()->traverse($nodes);
        $traversedNodes = $traverserFactory
            ->createMutationTraverser(
                new LabelMutationCandidatesVisitor(
                    '/path/to/source.php',
                    $sourceLineMatcher,
                ),
            )
            ->traverse($nodes);

        $actual = $this->dumper->dump(
            $traversedNodes,
            showLineNumbers: $changedLinesRange !== null,
        );

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'random example' => [
            file_get_contents(self::FIXTURES_DIR . '/TwoAdditions.php'),
            null,
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 9
                                            parent: nodeId(8)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(8)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: first
                                            eligible: true
                                            origNode: nodeId(9)
                                            mutationCandidate: true
                                        )
                                        returnType: Identifier(
                                            nodeId: 10
                                            parent: nodeId(8)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(8)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: first
                                            eligible: true
                                            origNode: nodeId(10)
                                            mutationCandidate: true
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Plus(
                                                    left: Scalar_Int(
                                                        rawValue: 1
                                                        kind: KIND_DEC (10)
                                                        nodeId: 13
                                                        parent: nodeId(12)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(8)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: first
                                                        eligible: true
                                                        origNode: nodeId(13)
                                                        mutationCandidate: true
                                                    )
                                                    right: Scalar_Int(
                                                        rawValue: 2
                                                        kind: KIND_DEC (10)
                                                        nodeId: 14
                                                        parent: nodeId(12)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(8)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: first
                                                        eligible: true
                                                        origNode: nodeId(14)
                                                        mutationCandidate: true
                                                    )
                                                    nodeId: 12
                                                    parent: nodeId(11)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(8)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: first
                                                    eligible: true
                                                    origNode: nodeId(12)
                                                    mutationCandidate: true
                                                )
                                                nodeId: 11
                                                parent: nodeId(8)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(8)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: first
                                                eligible: true
                                                origNode: nodeId(11)
                                                mutationCandidate: true
                                            )
                                        )
                                        nodeId: 8
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: first
                                        eligible: true
                                        origNode: nodeId(8)
                                        mutationCandidate: true
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 16
                                            parent: nodeId(15)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(15)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: second
                                            eligible: true
                                            origNode: nodeId(16)
                                            mutationCandidate: true
                                        )
                                        returnType: Identifier(
                                            nodeId: 17
                                            parent: nodeId(15)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(15)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: second
                                            eligible: true
                                            origNode: nodeId(17)
                                            mutationCandidate: true
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Minus(
                                                    left: Scalar_Int(
                                                        rawValue: 1
                                                        kind: KIND_DEC (10)
                                                        nodeId: 20
                                                        parent: nodeId(19)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(15)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: second
                                                        eligible: true
                                                        origNode: nodeId(20)
                                                        mutationCandidate: true
                                                    )
                                                    right: Scalar_Int(
                                                        rawValue: 2
                                                        kind: KIND_DEC (10)
                                                        nodeId: 21
                                                        parent: nodeId(19)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(15)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: second
                                                        eligible: true
                                                        origNode: nodeId(21)
                                                        mutationCandidate: true
                                                    )
                                                    nodeId: 19
                                                    parent: nodeId(18)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(15)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: second
                                                    eligible: true
                                                    origNode: nodeId(19)
                                                    mutationCandidate: true
                                                )
                                                nodeId: 18
                                                parent: nodeId(15)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(15)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: second
                                                eligible: true
                                                origNode: nodeId(18)
                                                mutationCandidate: true
                                            )
                                        )
                                        nodeId: 15
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: second
                                        eligible: true
                                        origNode: nodeId(15)
                                        mutationCandidate: true
                                    )
                                )
                                nodeId: 6
                                parent: nodeId(4)
                                eligible: true
                                origNode: nodeId(6)
                            )
                        )
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        yield 'function declaration' => [
            file_get_contents(self::FIXTURES_DIR . '/Function_.php'),
            null,
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Function(
                                name: Identifier(
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                params: array(
                                    0: Param(
                                        type: Identifier(
                                            nodeId: 9
                                            parent: nodeId(8)
                                            eligible: true
                                            origNode: nodeId(9)
                                        )
                                        var: Expr_Variable(
                                            nodeId: 10
                                            parent: nodeId(8)
                                            eligible: true
                                            origNode: nodeId(10)
                                        )
                                        nodeId: 8
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(8)
                                    )
                                    1: Param(
                                        type: Identifier(
                                            nodeId: 12
                                            parent: nodeId(11)
                                            eligible: true
                                            origNode: nodeId(12)
                                        )
                                        var: Expr_Variable(
                                            nodeId: 13
                                            parent: nodeId(11)
                                            eligible: true
                                            origNode: nodeId(13)
                                        )
                                        nodeId: 11
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(11)
                                    )
                                )
                                returnType: Identifier(
                                    nodeId: 14
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(14)
                                )
                                stmts: array(
                                    0: Stmt_Return(
                                        expr: Expr_BinaryOp_Identical(
                                            left: Expr_Variable(
                                                nodeId: 17
                                                parent: nodeId(16)
                                                eligible: true
                                                origNode: nodeId(17)
                                            )
                                            right: Expr_Variable(
                                                nodeId: 18
                                                parent: nodeId(16)
                                                eligible: true
                                                origNode: nodeId(18)
                                            )
                                            nodeId: 16
                                            parent: nodeId(15)
                                            eligible: true
                                            origNode: nodeId(16)
                                        )
                                        nodeId: 15
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(15)
                                    )
                                )
                                nodeId: 6
                                parent: nodeId(4)
                                isStrictTypes: true
                                eligible: true
                                origNode: nodeId(6)
                            )
                        )
                        kind: 1
                        nodeId: 4
                        eligible: true
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        yield 'trait declaration' => [
            file_get_contents(self::FIXTURES_DIR . '/TraitExample.php'),
            null,
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Trait(
                                name: Identifier(
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                stmts: array(
                                    0: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    nodeId: 10
                                                    parent: nodeId(9)
                                                    eligible: true
                                                    origNode: nodeId(10)
                                                )
                                                value: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: ''
                                                    nodeId: 11
                                                    parent: nodeId(9)
                                                    eligible: true
                                                    origNode: nodeId(11)
                                                )
                                                nodeId: 9
                                                parent: nodeId(8)
                                                eligible: true
                                                origNode: nodeId(9)
                                            )
                                        )
                                        nodeId: 8
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(8)
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 13
                                            origNode: nodeId(13)
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 15
                                                    origNode: nodeId(15)
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 16
                                                    origNode: nodeId(16)
                                                )
                                                nodeId: 14
                                                origNode: nodeId(14)
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 17
                                            origNode: nodeId(17)
                                        )
                                        nodeId: 12
                                        origNode: nodeId(12)
                                    )
                                    2: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 19
                                            parent: nodeId(18)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(18)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: concreteMethod
                                            eligible: true
                                            origNode: nodeId(19)
                                            mutationCandidate: true
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 21
                                                    parent: nodeId(20)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(18)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(21)
                                                    mutationCandidate: true
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 22
                                                    parent: nodeId(20)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(18)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(22)
                                                    mutationCandidate: true
                                                )
                                                nodeId: 20
                                                parent: nodeId(18)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                functionScope: nodeId(18)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: concreteMethod
                                                eligible: true
                                                origNode: nodeId(20)
                                                mutationCandidate: true
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 23
                                            parent: nodeId(18)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(18)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: concreteMethod
                                            eligible: true
                                            origNode: nodeId(23)
                                            mutationCandidate: true
                                        )
                                        nodeId: 18
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: concreteMethod
                                        eligible: true
                                        origNode: nodeId(18)
                                        mutationCandidate: true
                                    )
                                )
                                nodeId: 6
                                parent: nodeId(4)
                                eligible: true
                                next: nodeId(8)
                                origNode: nodeId(6)
                            )
                        )
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        yield 'interface declaration' => [
            file_get_contents(self::FIXTURES_DIR . '/InterfaceExample.php'),
            null,
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Interface(
                                name: Identifier(
                                    nodeId: 7
                                    origNode: nodeId(7)
                                )
                                stmts: array(
                                    0: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    nodeId: 10
                                                    origNode: nodeId(10)
                                                )
                                                value: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: ''
                                                    nodeId: 11
                                                    origNode: nodeId(11)
                                                )
                                                nodeId: 9
                                                origNode: nodeId(9)
                                            )
                                        )
                                        nodeId: 8
                                        origNode: nodeId(8)
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 13
                                            origNode: nodeId(13)
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 15
                                                    origNode: nodeId(15)
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 16
                                                    origNode: nodeId(16)
                                                )
                                                nodeId: 14
                                                origNode: nodeId(14)
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 17
                                            origNode: nodeId(17)
                                        )
                                        nodeId: 12
                                        origNode: nodeId(12)
                                    )
                                )
                                nodeId: 6
                                origNode: nodeId(6)
                            )
                        )
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        yield 'concrete class' => [
            file_get_contents(self::FIXTURES_DIR . '/ConcreteClass.php'),
            null,
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                stmts: array(
                                    0: Stmt_TraitUse(
                                        traits: array(
                                            0: Name(
                                                nodeId: 9
                                                resolvedName: nodeId(9)
                                                parent: nodeId(8)
                                                eligible: true
                                                origNode: nodeId(9)
                                            )
                                        )
                                        nodeId: 8
                                        parent: nodeId(6)
                                        eligible: true
                                        next: nodeId(10)
                                        origNode: nodeId(8)
                                    )
                                    1: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    nodeId: 12
                                                    parent: nodeId(11)
                                                    eligible: true
                                                    origNode: nodeId(12)
                                                )
                                                value: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: ''
                                                    nodeId: 13
                                                    parent: nodeId(11)
                                                    eligible: true
                                                    origNode: nodeId(13)
                                                )
                                                nodeId: 11
                                                parent: nodeId(10)
                                                eligible: true
                                                origNode: nodeId(11)
                                            )
                                        )
                                        nodeId: 10
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(10)
                                    )
                                    2: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 15
                                            parent: nodeId(14)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(14)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: concreteMethod
                                            eligible: true
                                            origNode: nodeId(15)
                                            mutationCandidate: true
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 17
                                                    parent: nodeId(16)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(14)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(17)
                                                    mutationCandidate: true
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 18
                                                    parent: nodeId(16)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(14)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(18)
                                                    mutationCandidate: true
                                                )
                                                nodeId: 16
                                                parent: nodeId(14)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                functionScope: nodeId(14)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: concreteMethod
                                                eligible: true
                                                origNode: nodeId(16)
                                                mutationCandidate: true
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 19
                                            parent: nodeId(14)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(14)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: concreteMethod
                                            eligible: true
                                            origNode: nodeId(19)
                                            mutationCandidate: true
                                        )
                                        stmts: array(
                                            0: Stmt_If(
                                                cond: Expr_BinaryOp_Identical(
                                                    left: Expr_Variable(
                                                        nodeId: 22
                                                        parent: nodeId(21)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(14)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: concreteMethod
                                                        eligible: true
                                                        origNode: nodeId(22)
                                                        mutationCandidate: true
                                                    )
                                                    right: Expr_ConstFetch(
                                                        name: Name(
                                                            nodeId: 24
                                                            namespacedName: nodeId(24)
                                                            parent: nodeId(23)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            functionScope: nodeId(14)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: concreteMethod
                                                            eligible: true
                                                            origNode: nodeId(24)
                                                            mutationCandidate: true
                                                        )
                                                        nodeId: 23
                                                        parent: nodeId(21)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(14)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: concreteMethod
                                                        eligible: true
                                                        origNode: nodeId(23)
                                                        mutationCandidate: true
                                                    )
                                                    nodeId: 21
                                                    parent: nodeId(20)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(14)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(21)
                                                    mutationCandidate: true
                                                )
                                                stmts: array(
                                                    0: Stmt_Echo(
                                                        exprs: array(
                                                            0: Scalar_String(
                                                                kind: KIND_SINGLE_QUOTED (1)
                                                                rawValue: 'nothing to do'
                                                                nodeId: 26
                                                                parent: nodeId(25)
                                                                isInsideFunction: true
                                                                isStrictTypes: true
                                                                functionScope: nodeId(14)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: concreteMethod
                                                                eligible: true
                                                                origNode: nodeId(26)
                                                                mutationCandidate: true
                                                            )
                                                        )
                                                        nodeId: 25
                                                        parent: nodeId(20)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(14)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: concreteMethod
                                                        eligible: true
                                                        next: nodeId(27)
                                                        origNode: nodeId(25)
                                                        mutationCandidate: true
                                                    )
                                                )
                                                else: Stmt_Else(
                                                    stmts: array(
                                                        0: Stmt_Expression(
                                                            expr: Expr_FuncCall(
                                                                name: Expr_Variable(
                                                                    nodeId: 30
                                                                    parent: nodeId(29)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: true
                                                                    functionScope: nodeId(14)
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: concreteMethod
                                                                    eligible: true
                                                                    origNode: nodeId(30)
                                                                    mutationCandidate: true
                                                                )
                                                                nodeId: 29
                                                                parent: nodeId(28)
                                                                isInsideFunction: true
                                                                isStrictTypes: true
                                                                functionScope: nodeId(14)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: concreteMethod
                                                                eligible: true
                                                                origNode: nodeId(29)
                                                                mutationCandidate: true
                                                            )
                                                            nodeId: 28
                                                            parent: nodeId(27)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            functionScope: nodeId(14)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: concreteMethod
                                                            eligible: true
                                                            origNode: nodeId(28)
                                                            mutationCandidate: true
                                                        )
                                                    )
                                                    nodeId: 27
                                                    parent: nodeId(20)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(14)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    next: nodeId(28)
                                                    origNode: nodeId(27)
                                                    mutationCandidate: true
                                                )
                                                nodeId: 20
                                                parent: nodeId(14)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(14)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: concreteMethod
                                                eligible: true
                                                next: nodeId(25)
                                                origNode: nodeId(20)
                                                mutationCandidate: true
                                            )
                                        )
                                        nodeId: 14
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: concreteMethod
                                        eligible: true
                                        origNode: nodeId(14)
                                        mutationCandidate: true
                                    )
                                    3: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 32
                                            parent: nodeId(31)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(31)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: abstractMethod
                                            eligible: true
                                            origNode: nodeId(32)
                                            mutationCandidate: true
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 34
                                                    parent: nodeId(33)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(31)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: abstractMethod
                                                    eligible: true
                                                    origNode: nodeId(34)
                                                    mutationCandidate: true
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 35
                                                    parent: nodeId(33)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(31)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: abstractMethod
                                                    eligible: true
                                                    origNode: nodeId(35)
                                                    mutationCandidate: true
                                                )
                                                nodeId: 33
                                                parent: nodeId(31)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                functionScope: nodeId(31)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: abstractMethod
                                                eligible: true
                                                origNode: nodeId(33)
                                                mutationCandidate: true
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 36
                                            parent: nodeId(31)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(31)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: abstractMethod
                                            eligible: true
                                            origNode: nodeId(36)
                                            mutationCandidate: true
                                        )
                                        nodeId: 31
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: abstractMethod
                                        eligible: true
                                        origNode: nodeId(31)
                                        mutationCandidate: true
                                    )
                                )
                                nodeId: 6
                                parent: nodeId(4)
                                eligible: true
                                next: nodeId(8)
                                origNode: nodeId(6)
                            )
                        )
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        yield 'concrete class with one line of a method changed' => [
            file_get_contents(self::FIXTURES_DIR . '/ConcreteClass.php'),
            [ChangedLinesRange::forLine(46)],
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    startLine: 34
                                    endLine: 34
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    startLine: 34
                                    endLine: 34
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                startLine: 34
                                endLine: 34
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        startLine: 34
                        endLine: 34
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            startLine: 36
                            endLine: 36
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    startLine: 38
                                    endLine: 38
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                stmts: array(
                                    0: Stmt_TraitUse(
                                        traits: array(
                                            0: Name(
                                                startLine: 40
                                                endLine: 40
                                                nodeId: 9
                                                resolvedName: nodeId(9)
                                                parent: nodeId(8)
                                                eligible: true
                                                origNode: nodeId(9)
                                            )
                                        )
                                        startLine: 40
                                        endLine: 40
                                        nodeId: 8
                                        parent: nodeId(6)
                                        eligible: true
                                        next: nodeId(10)
                                        origNode: nodeId(8)
                                    )
                                    1: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    startLine: 42
                                                    endLine: 42
                                                    nodeId: 12
                                                    parent: nodeId(11)
                                                    eligible: true
                                                    origNode: nodeId(12)
                                                )
                                                value: Scalar_String(
                                                    startLine: 42
                                                    endLine: 42
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: ''
                                                    nodeId: 13
                                                    parent: nodeId(11)
                                                    eligible: true
                                                    origNode: nodeId(13)
                                                )
                                                startLine: 42
                                                endLine: 42
                                                nodeId: 11
                                                parent: nodeId(10)
                                                eligible: true
                                                origNode: nodeId(11)
                                            )
                                        )
                                        startLine: 42
                                        endLine: 42
                                        nodeId: 10
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(10)
                                    )
                                    2: Stmt_ClassMethod(
                                        name: Identifier(
                                            startLine: 44
                                            endLine: 44
                                            nodeId: 15
                                            parent: nodeId(14)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(14)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: concreteMethod
                                            eligible: true
                                            origNode: nodeId(15)
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    startLine: 44
                                                    endLine: 44
                                                    nodeId: 17
                                                    parent: nodeId(16)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(14)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(17)
                                                )
                                                var: Expr_Variable(
                                                    startLine: 44
                                                    endLine: 44
                                                    nodeId: 18
                                                    parent: nodeId(16)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(14)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(18)
                                                )
                                                startLine: 44
                                                endLine: 44
                                                nodeId: 16
                                                parent: nodeId(14)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                functionScope: nodeId(14)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: concreteMethod
                                                eligible: true
                                                origNode: nodeId(16)
                                            )
                                        )
                                        returnType: Identifier(
                                            startLine: 44
                                            endLine: 44
                                            nodeId: 19
                                            parent: nodeId(14)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(14)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: concreteMethod
                                            eligible: true
                                            origNode: nodeId(19)
                                        )
                                        stmts: array(
                                            0: Stmt_If(
                                                cond: Expr_BinaryOp_Identical(
                                                    left: Expr_Variable(
                                                        startLine: 46
                                                        endLine: 46
                                                        nodeId: 22
                                                        parent: nodeId(21)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(14)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: concreteMethod
                                                        eligible: true
                                                        origNode: nodeId(22)
                                                        mutationCandidate: true
                                                    )
                                                    right: Expr_ConstFetch(
                                                        name: Name(
                                                            startLine: 46
                                                            endLine: 46
                                                            nodeId: 24
                                                            namespacedName: nodeId(24)
                                                            parent: nodeId(23)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            functionScope: nodeId(14)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: concreteMethod
                                                            eligible: true
                                                            origNode: nodeId(24)
                                                            mutationCandidate: true
                                                        )
                                                        startLine: 46
                                                        endLine: 46
                                                        nodeId: 23
                                                        parent: nodeId(21)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(14)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: concreteMethod
                                                        eligible: true
                                                        origNode: nodeId(23)
                                                        mutationCandidate: true
                                                    )
                                                    startLine: 46
                                                    endLine: 46
                                                    nodeId: 21
                                                    parent: nodeId(20)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(14)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(21)
                                                    mutationCandidate: true
                                                )
                                                stmts: array(
                                                    0: Stmt_Echo(
                                                        exprs: array(
                                                            0: Scalar_String(
                                                                startLine: 47
                                                                endLine: 47
                                                                kind: KIND_SINGLE_QUOTED (1)
                                                                rawValue: 'nothing to do'
                                                                nodeId: 26
                                                                parent: nodeId(25)
                                                                isInsideFunction: true
                                                                isStrictTypes: true
                                                                functionScope: nodeId(14)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: concreteMethod
                                                                eligible: true
                                                                origNode: nodeId(26)
                                                            )
                                                        )
                                                        startLine: 47
                                                        endLine: 47
                                                        nodeId: 25
                                                        parent: nodeId(20)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(14)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: concreteMethod
                                                        eligible: true
                                                        next: nodeId(27)
                                                        origNode: nodeId(25)
                                                    )
                                                )
                                                else: Stmt_Else(
                                                    stmts: array(
                                                        0: Stmt_Expression(
                                                            expr: Expr_FuncCall(
                                                                name: Expr_Variable(
                                                                    startLine: 49
                                                                    endLine: 49
                                                                    nodeId: 30
                                                                    parent: nodeId(29)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: true
                                                                    functionScope: nodeId(14)
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: concreteMethod
                                                                    eligible: true
                                                                    origNode: nodeId(30)
                                                                )
                                                                startLine: 49
                                                                endLine: 49
                                                                nodeId: 29
                                                                parent: nodeId(28)
                                                                isInsideFunction: true
                                                                isStrictTypes: true
                                                                functionScope: nodeId(14)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: concreteMethod
                                                                eligible: true
                                                                origNode: nodeId(29)
                                                            )
                                                            startLine: 49
                                                            endLine: 49
                                                            nodeId: 28
                                                            parent: nodeId(27)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            functionScope: nodeId(14)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: concreteMethod
                                                            eligible: true
                                                            origNode: nodeId(28)
                                                        )
                                                    )
                                                    startLine: 48
                                                    endLine: 50
                                                    nodeId: 27
                                                    parent: nodeId(20)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(14)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    next: nodeId(28)
                                                    origNode: nodeId(27)
                                                )
                                                startLine: 46
                                                endLine: 50
                                                nodeId: 20
                                                parent: nodeId(14)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(14)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: concreteMethod
                                                eligible: true
                                                next: nodeId(25)
                                                origNode: nodeId(20)
                                                mutationCandidate: true
                                            )
                                        )
                                        startLine: 44
                                        endLine: 51
                                        nodeId: 14
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: concreteMethod
                                        eligible: true
                                        origNode: nodeId(14)
                                        mutationCandidate: true
                                    )
                                    3: Stmt_ClassMethod(
                                        name: Identifier(
                                            startLine: 53
                                            endLine: 53
                                            nodeId: 32
                                            parent: nodeId(31)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(31)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: abstractMethod
                                            eligible: true
                                            origNode: nodeId(32)
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    startLine: 53
                                                    endLine: 53
                                                    nodeId: 34
                                                    parent: nodeId(33)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(31)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: abstractMethod
                                                    eligible: true
                                                    origNode: nodeId(34)
                                                )
                                                var: Expr_Variable(
                                                    startLine: 53
                                                    endLine: 53
                                                    nodeId: 35
                                                    parent: nodeId(33)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(31)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: abstractMethod
                                                    eligible: true
                                                    origNode: nodeId(35)
                                                )
                                                startLine: 53
                                                endLine: 53
                                                nodeId: 33
                                                parent: nodeId(31)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                functionScope: nodeId(31)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: abstractMethod
                                                eligible: true
                                                origNode: nodeId(33)
                                            )
                                        )
                                        returnType: Identifier(
                                            startLine: 53
                                            endLine: 53
                                            nodeId: 36
                                            parent: nodeId(31)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(31)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: abstractMethod
                                            eligible: true
                                            origNode: nodeId(36)
                                        )
                                        startLine: 53
                                        endLine: 55
                                        nodeId: 31
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: abstractMethod
                                        eligible: true
                                        origNode: nodeId(31)
                                    )
                                )
                                startLine: 38
                                endLine: 56
                                nodeId: 6
                                parent: nodeId(4)
                                eligible: true
                                next: nodeId(8)
                                origNode: nodeId(6)
                            )
                        )
                        startLine: 36
                        endLine: 56
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        yield 'class with an abstract method' => [
            file_get_contents(self::FIXTURES_DIR . '/AbstractMethod.php'),
            null,
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                stmts: array(
                                    0: Stmt_TraitUse(
                                        traits: array(
                                            0: Name(
                                                nodeId: 9
                                                resolvedName: nodeId(9)
                                                parent: nodeId(8)
                                                eligible: true
                                                origNode: nodeId(9)
                                            )
                                        )
                                        nodeId: 8
                                        parent: nodeId(6)
                                        eligible: true
                                        next: nodeId(10)
                                        origNode: nodeId(8)
                                    )
                                    1: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    nodeId: 12
                                                    parent: nodeId(11)
                                                    eligible: true
                                                    origNode: nodeId(12)
                                                )
                                                value: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: ''
                                                    nodeId: 13
                                                    parent: nodeId(11)
                                                    eligible: true
                                                    origNode: nodeId(13)
                                                )
                                                nodeId: 11
                                                parent: nodeId(10)
                                                eligible: true
                                                origNode: nodeId(11)
                                            )
                                        )
                                        nodeId: 10
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(10)
                                    )
                                    2: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 15
                                            origNode: nodeId(15)
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 17
                                                    origNode: nodeId(17)
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 18
                                                    origNode: nodeId(18)
                                                )
                                                nodeId: 16
                                                origNode: nodeId(16)
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 19
                                            origNode: nodeId(19)
                                        )
                                        nodeId: 14
                                        origNode: nodeId(14)
                                    )
                                    3: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 21
                                            parent: nodeId(20)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(20)
                                            reflectionClass: Infection\Reflection\NullReflection
                                            functionName: concreteMethod
                                            eligible: true
                                            origNode: nodeId(21)
                                            mutationCandidate: true
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 23
                                                    parent: nodeId(22)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(20)
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(23)
                                                    mutationCandidate: true
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 24
                                                    parent: nodeId(22)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    functionScope: nodeId(20)
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                    functionName: concreteMethod
                                                    eligible: true
                                                    origNode: nodeId(24)
                                                    mutationCandidate: true
                                                )
                                                nodeId: 22
                                                parent: nodeId(20)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                functionScope: nodeId(20)
                                                reflectionClass: Infection\Reflection\NullReflection
                                                functionName: concreteMethod
                                                eligible: true
                                                origNode: nodeId(22)
                                                mutationCandidate: true
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 25
                                            parent: nodeId(20)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(20)
                                            reflectionClass: Infection\Reflection\NullReflection
                                            functionName: concreteMethod
                                            eligible: true
                                            origNode: nodeId(25)
                                            mutationCandidate: true
                                        )
                                        nodeId: 20
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\NullReflection
                                        functionName: concreteMethod
                                        eligible: true
                                        origNode: nodeId(20)
                                        mutationCandidate: true
                                    )
                                )
                                nodeId: 6
                                parent: nodeId(4)
                                eligible: true
                                next: nodeId(8)
                                origNode: nodeId(6)
                            )
                        )
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];
    }
}
