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
                                    eligible: true
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    eligible: true
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                    rawValue: 1
                                )
                                eligible: true
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                            )
                        )
                        eligible: true
                        next: nodeId(4)
                        nodeId: 0
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: true
                                    nodeId: 7
                                    origNode: nodeId(7)
                                    parent: nodeId(6)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            functionName: first
                                            functionScope: nodeId(8)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 9
                                            origNode: nodeId(9)
                                            parent: nodeId(8)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            functionName: first
                                            functionScope: nodeId(8)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 10
                                            origNode: nodeId(10)
                                            parent: nodeId(8)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Plus(
                                                    left: Scalar_Int(
                                                        eligible: true
                                                        functionName: first
                                                        functionScope: nodeId(8)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        kind: KIND_DEC (10)
                                                        mutationCandidate: true
                                                        nodeId: 13
                                                        origNode: nodeId(13)
                                                        parent: nodeId(12)
                                                        rawValue: 1
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                    )
                                                    right: Scalar_Int(
                                                        eligible: true
                                                        functionName: first
                                                        functionScope: nodeId(8)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        kind: KIND_DEC (10)
                                                        mutationCandidate: true
                                                        nodeId: 14
                                                        origNode: nodeId(14)
                                                        parent: nodeId(12)
                                                        rawValue: 2
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                    )
                                                    eligible: true
                                                    functionName: first
                                                    functionScope: nodeId(8)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 12
                                                    origNode: nodeId(12)
                                                    parent: nodeId(11)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                eligible: true
                                                functionName: first
                                                functionScope: nodeId(8)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                nodeId: 11
                                                origNode: nodeId(11)
                                                parent: nodeId(8)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                            )
                                        )
                                        eligible: true
                                        functionName: first
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        mutationCandidate: true
                                        nodeId: 8
                                        origNode: nodeId(8)
                                        parent: nodeId(6)
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            functionName: second
                                            functionScope: nodeId(15)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 16
                                            origNode: nodeId(16)
                                            parent: nodeId(15)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            functionName: second
                                            functionScope: nodeId(15)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 17
                                            origNode: nodeId(17)
                                            parent: nodeId(15)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Minus(
                                                    left: Scalar_Int(
                                                        eligible: true
                                                        functionName: second
                                                        functionScope: nodeId(15)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        kind: KIND_DEC (10)
                                                        mutationCandidate: true
                                                        nodeId: 20
                                                        origNode: nodeId(20)
                                                        parent: nodeId(19)
                                                        rawValue: 1
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                    )
                                                    right: Scalar_Int(
                                                        eligible: true
                                                        functionName: second
                                                        functionScope: nodeId(15)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        kind: KIND_DEC (10)
                                                        mutationCandidate: true
                                                        nodeId: 21
                                                        origNode: nodeId(21)
                                                        parent: nodeId(19)
                                                        rawValue: 2
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                    )
                                                    eligible: true
                                                    functionName: second
                                                    functionScope: nodeId(15)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 19
                                                    origNode: nodeId(19)
                                                    parent: nodeId(18)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                eligible: true
                                                functionName: second
                                                functionScope: nodeId(15)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                nodeId: 18
                                                origNode: nodeId(18)
                                                parent: nodeId(15)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                            )
                                        )
                                        eligible: true
                                        functionName: second
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        mutationCandidate: true
                                        nodeId: 15
                                        origNode: nodeId(15)
                                        parent: nodeId(6)
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                    )
                                )
                                eligible: true
                                nodeId: 6
                                origNode: nodeId(6)
                                parent: nodeId(4)
                            )
                        )
                        eligible: true
                        kind: 1
                        next: nodeId(6)
                        nodeId: 4
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
                                    eligible: true
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    eligible: true
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                    rawValue: 1
                                )
                                eligible: true
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                            )
                        )
                        eligible: true
                        next: nodeId(4)
                        nodeId: 0
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
                        )
                        stmts: array(
                            0: Stmt_Function(
                                name: Identifier(
                                    eligible: true
                                    nodeId: 7
                                    origNode: nodeId(7)
                                    parent: nodeId(6)
                                )
                                params: array(
                                    0: Param(
                                        type: Identifier(
                                            eligible: true
                                            nodeId: 9
                                            origNode: nodeId(9)
                                            parent: nodeId(8)
                                        )
                                        var: Expr_Variable(
                                            eligible: true
                                            nodeId: 10
                                            origNode: nodeId(10)
                                            parent: nodeId(8)
                                        )
                                        eligible: true
                                        nodeId: 8
                                        origNode: nodeId(8)
                                        parent: nodeId(6)
                                    )
                                    1: Param(
                                        type: Identifier(
                                            eligible: true
                                            nodeId: 12
                                            origNode: nodeId(12)
                                            parent: nodeId(11)
                                        )
                                        var: Expr_Variable(
                                            eligible: true
                                            nodeId: 13
                                            origNode: nodeId(13)
                                            parent: nodeId(11)
                                        )
                                        eligible: true
                                        nodeId: 11
                                        origNode: nodeId(11)
                                        parent: nodeId(6)
                                    )
                                )
                                returnType: Identifier(
                                    eligible: true
                                    nodeId: 14
                                    origNode: nodeId(14)
                                    parent: nodeId(6)
                                )
                                stmts: array(
                                    0: Stmt_Return(
                                        expr: Expr_BinaryOp_Identical(
                                            left: Expr_Variable(
                                                eligible: true
                                                nodeId: 17
                                                origNode: nodeId(17)
                                                parent: nodeId(16)
                                            )
                                            right: Expr_Variable(
                                                eligible: true
                                                nodeId: 18
                                                origNode: nodeId(18)
                                                parent: nodeId(16)
                                            )
                                            eligible: true
                                            nodeId: 16
                                            origNode: nodeId(16)
                                            parent: nodeId(15)
                                        )
                                        eligible: true
                                        nodeId: 15
                                        origNode: nodeId(15)
                                        parent: nodeId(6)
                                    )
                                )
                                eligible: true
                                isStrictTypes: true
                                nodeId: 6
                                origNode: nodeId(6)
                                parent: nodeId(4)
                            )
                        )
                        eligible: true
                        kind: 1
                        nodeId: 4
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
                                    eligible: true
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    eligible: true
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                    rawValue: 1
                                )
                                eligible: true
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                            )
                        )
                        eligible: true
                        next: nodeId(4)
                        nodeId: 0
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
                        )
                        stmts: array(
                            0: Stmt_Trait(
                                name: Identifier(
                                    eligible: true
                                    nodeId: 7
                                    origNode: nodeId(7)
                                    parent: nodeId(6)
                                )
                                stmts: array(
                                    0: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    eligible: true
                                                    nodeId: 10
                                                    origNode: nodeId(10)
                                                    parent: nodeId(9)
                                                )
                                                value: Scalar_String(
                                                    eligible: true
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    nodeId: 11
                                                    origNode: nodeId(11)
                                                    parent: nodeId(9)
                                                    rawValue: ''
                                                )
                                                eligible: true
                                                nodeId: 9
                                                origNode: nodeId(9)
                                                parent: nodeId(8)
                                            )
                                        )
                                        eligible: true
                                        nodeId: 8
                                        origNode: nodeId(8)
                                        parent: nodeId(6)
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
                                        eligible: true
                                        nodeId: 12
                                        origNode: nodeId(12)
                                    )
                                    2: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            functionName: concreteMethod
                                            functionScope: nodeId(18)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 19
                                            origNode: nodeId(19)
                                            parent: nodeId(18)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    eligible: true
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(18)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 21
                                                    origNode: nodeId(21)
                                                    parent: nodeId(20)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                var: Expr_Variable(
                                                    eligible: true
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(18)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 22
                                                    origNode: nodeId(22)
                                                    parent: nodeId(20)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                eligible: true
                                                functionName: concreteMethod
                                                functionScope: nodeId(18)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                nodeId: 20
                                                origNode: nodeId(20)
                                                parent: nodeId(18)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                            )
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            functionName: concreteMethod
                                            functionScope: nodeId(18)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 23
                                            origNode: nodeId(23)
                                            parent: nodeId(18)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        eligible: true
                                        functionName: concreteMethod
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        mutationCandidate: true
                                        nodeId: 18
                                        origNode: nodeId(18)
                                        parent: nodeId(6)
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                    )
                                )
                                eligible: true
                                next: nodeId(8)
                                nodeId: 6
                                origNode: nodeId(6)
                                parent: nodeId(4)
                            )
                        )
                        eligible: true
                        kind: 1
                        next: nodeId(6)
                        nodeId: 4
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
                                    eligible: true
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    eligible: true
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                    rawValue: 1
                                )
                                eligible: true
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                            )
                        )
                        eligible: true
                        next: nodeId(4)
                        nodeId: 0
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
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
                                                    nodeId: 11
                                                    origNode: nodeId(11)
                                                    rawValue: ''
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
                                eligible: true
                                nodeId: 6
                                origNode: nodeId(6)
                            )
                        )
                        eligible: true
                        kind: 1
                        next: nodeId(6)
                        nodeId: 4
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
                                    eligible: true
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    eligible: true
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                    rawValue: 1
                                )
                                eligible: true
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                            )
                        )
                        eligible: true
                        next: nodeId(4)
                        nodeId: 0
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: true
                                    nodeId: 7
                                    origNode: nodeId(7)
                                    parent: nodeId(6)
                                )
                                stmts: array(
                                    0: Stmt_TraitUse(
                                        traits: array(
                                            0: Name(
                                                eligible: true
                                                nodeId: 9
                                                origNode: nodeId(9)
                                                parent: nodeId(8)
                                                resolvedName: FullyQualified(Infection\Tests\PhpParser\Visitor\EnrichmentTraverse\Fixtures\TraitExample)
                                            )
                                        )
                                        eligible: true
                                        next: nodeId(10)
                                        nodeId: 8
                                        origNode: nodeId(8)
                                        parent: nodeId(6)
                                    )
                                    1: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    eligible: true
                                                    nodeId: 12
                                                    origNode: nodeId(12)
                                                    parent: nodeId(11)
                                                )
                                                value: Scalar_String(
                                                    eligible: true
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    nodeId: 13
                                                    origNode: nodeId(13)
                                                    parent: nodeId(11)
                                                    rawValue: ''
                                                )
                                                eligible: true
                                                nodeId: 11
                                                origNode: nodeId(11)
                                                parent: nodeId(10)
                                            )
                                        )
                                        eligible: true
                                        nodeId: 10
                                        origNode: nodeId(10)
                                        parent: nodeId(6)
                                    )
                                    2: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            functionName: concreteMethod
                                            functionScope: nodeId(14)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 15
                                            origNode: nodeId(15)
                                            parent: nodeId(14)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    eligible: true
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(14)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 17
                                                    origNode: nodeId(17)
                                                    parent: nodeId(16)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                var: Expr_Variable(
                                                    eligible: true
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(14)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 18
                                                    origNode: nodeId(18)
                                                    parent: nodeId(16)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                eligible: true
                                                functionName: concreteMethod
                                                functionScope: nodeId(14)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                nodeId: 16
                                                origNode: nodeId(16)
                                                parent: nodeId(14)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                            )
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            functionName: concreteMethod
                                            functionScope: nodeId(14)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 19
                                            origNode: nodeId(19)
                                            parent: nodeId(14)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        stmts: array(
                                            0: Stmt_If(
                                                cond: Expr_BinaryOp_Identical(
                                                    left: Expr_Variable(
                                                        eligible: true
                                                        functionName: concreteMethod
                                                        functionScope: nodeId(14)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        mutationCandidate: true
                                                        nodeId: 22
                                                        origNode: nodeId(22)
                                                        parent: nodeId(21)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                    )
                                                    right: Expr_ConstFetch(
                                                        name: Name(
                                                            eligible: true
                                                            functionName: concreteMethod
                                                            functionScope: nodeId(14)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            mutationCandidate: true
                                                            namespacedName: FullyQualified(Infection\Tests\PhpParser\Visitor\EnrichmentTraverse\Fixtures\null)
                                                            nodeId: 24
                                                            origNode: nodeId(24)
                                                            parent: nodeId(23)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                        )
                                                        eligible: true
                                                        functionName: concreteMethod
                                                        functionScope: nodeId(14)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        mutationCandidate: true
                                                        nodeId: 23
                                                        origNode: nodeId(23)
                                                        parent: nodeId(21)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                    )
                                                    eligible: true
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(14)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 21
                                                    origNode: nodeId(21)
                                                    parent: nodeId(20)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                stmts: array(
                                                    0: Stmt_Echo(
                                                        exprs: array(
                                                            0: Scalar_String(
                                                                eligible: true
                                                                functionName: concreteMethod
                                                                functionScope: nodeId(14)
                                                                isInsideFunction: true
                                                                isStrictTypes: true
                                                                kind: KIND_SINGLE_QUOTED (1)
                                                                mutationCandidate: true
                                                                nodeId: 26
                                                                origNode: nodeId(26)
                                                                parent: nodeId(25)
                                                                rawValue: 'nothing to do'
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                            )
                                                        )
                                                        eligible: true
                                                        functionName: concreteMethod
                                                        functionScope: nodeId(14)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        mutationCandidate: true
                                                        next: nodeId(27)
                                                        nodeId: 25
                                                        origNode: nodeId(25)
                                                        parent: nodeId(20)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                    )
                                                )
                                                else: Stmt_Else(
                                                    stmts: array(
                                                        0: Stmt_Expression(
                                                            expr: Expr_FuncCall(
                                                                name: Expr_Variable(
                                                                    eligible: true
                                                                    functionName: concreteMethod
                                                                    functionScope: nodeId(14)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: true
                                                                    mutationCandidate: true
                                                                    nodeId: 30
                                                                    origNode: nodeId(30)
                                                                    parent: nodeId(29)
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                )
                                                                eligible: true
                                                                functionName: concreteMethod
                                                                functionScope: nodeId(14)
                                                                isInsideFunction: true
                                                                isStrictTypes: true
                                                                mutationCandidate: true
                                                                nodeId: 29
                                                                origNode: nodeId(29)
                                                                parent: nodeId(28)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                            )
                                                            eligible: true
                                                            functionName: concreteMethod
                                                            functionScope: nodeId(14)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            mutationCandidate: true
                                                            nodeId: 28
                                                            origNode: nodeId(28)
                                                            parent: nodeId(27)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                        )
                                                    )
                                                    eligible: true
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(14)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    next: nodeId(28)
                                                    nodeId: 27
                                                    origNode: nodeId(27)
                                                    parent: nodeId(20)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                eligible: true
                                                functionName: concreteMethod
                                                functionScope: nodeId(14)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                next: nodeId(25)
                                                nodeId: 20
                                                origNode: nodeId(20)
                                                parent: nodeId(14)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                            )
                                        )
                                        eligible: true
                                        functionName: concreteMethod
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        mutationCandidate: true
                                        nodeId: 14
                                        origNode: nodeId(14)
                                        parent: nodeId(6)
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                    )
                                    3: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            functionName: abstractMethod
                                            functionScope: nodeId(31)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 32
                                            origNode: nodeId(32)
                                            parent: nodeId(31)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    eligible: true
                                                    functionName: abstractMethod
                                                    functionScope: nodeId(31)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 34
                                                    origNode: nodeId(34)
                                                    parent: nodeId(33)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                var: Expr_Variable(
                                                    eligible: true
                                                    functionName: abstractMethod
                                                    functionScope: nodeId(31)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 35
                                                    origNode: nodeId(35)
                                                    parent: nodeId(33)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                )
                                                eligible: true
                                                functionName: abstractMethod
                                                functionScope: nodeId(31)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                nodeId: 33
                                                origNode: nodeId(33)
                                                parent: nodeId(31)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                            )
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            functionName: abstractMethod
                                            functionScope: nodeId(31)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 36
                                            origNode: nodeId(36)
                                            parent: nodeId(31)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                        )
                                        eligible: true
                                        functionName: abstractMethod
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        mutationCandidate: true
                                        nodeId: 31
                                        origNode: nodeId(31)
                                        parent: nodeId(6)
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                    )
                                )
                                eligible: true
                                next: nodeId(8)
                                nodeId: 6
                                origNode: nodeId(6)
                                parent: nodeId(4)
                            )
                        )
                        eligible: true
                        kind: 1
                        next: nodeId(6)
                        nodeId: 4
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
                                    eligible: true
                                    endLine: 34
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                    startLine: 34
                                )
                                value: Scalar_Int(
                                    eligible: true
                                    endLine: 34
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                    rawValue: 1
                                    startLine: 34
                                )
                                eligible: true
                                endLine: 34
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                                startLine: 34
                            )
                        )
                        eligible: true
                        endLine: 34
                        next: nodeId(4)
                        nodeId: 0
                        origNode: nodeId(0)
                        startLine: 34
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            endLine: 36
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
                            startLine: 36
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: true
                                    endLine: 38
                                    nodeId: 7
                                    origNode: nodeId(7)
                                    parent: nodeId(6)
                                    startLine: 38
                                )
                                stmts: array(
                                    0: Stmt_TraitUse(
                                        traits: array(
                                            0: Name(
                                                eligible: true
                                                endLine: 40
                                                nodeId: 9
                                                origNode: nodeId(9)
                                                parent: nodeId(8)
                                                resolvedName: FullyQualified(Infection\Tests\PhpParser\Visitor\EnrichmentTraverse\Fixtures\TraitExample)
                                                startLine: 40
                                            )
                                        )
                                        eligible: true
                                        endLine: 40
                                        next: nodeId(10)
                                        nodeId: 8
                                        origNode: nodeId(8)
                                        parent: nodeId(6)
                                        startLine: 40
                                    )
                                    1: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    eligible: true
                                                    endLine: 42
                                                    nodeId: 12
                                                    origNode: nodeId(12)
                                                    parent: nodeId(11)
                                                    startLine: 42
                                                )
                                                value: Scalar_String(
                                                    eligible: true
                                                    endLine: 42
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    nodeId: 13
                                                    origNode: nodeId(13)
                                                    parent: nodeId(11)
                                                    rawValue: ''
                                                    startLine: 42
                                                )
                                                eligible: true
                                                endLine: 42
                                                nodeId: 11
                                                origNode: nodeId(11)
                                                parent: nodeId(10)
                                                startLine: 42
                                            )
                                        )
                                        eligible: true
                                        endLine: 42
                                        nodeId: 10
                                        origNode: nodeId(10)
                                        parent: nodeId(6)
                                        startLine: 42
                                    )
                                    2: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            endLine: 44
                                            functionName: concreteMethod
                                            functionScope: nodeId(14)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            nodeId: 15
                                            origNode: nodeId(15)
                                            parent: nodeId(14)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            startLine: 44
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    eligible: true
                                                    endLine: 44
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(14)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    nodeId: 17
                                                    origNode: nodeId(17)
                                                    parent: nodeId(16)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    startLine: 44
                                                )
                                                var: Expr_Variable(
                                                    eligible: true
                                                    endLine: 44
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(14)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    nodeId: 18
                                                    origNode: nodeId(18)
                                                    parent: nodeId(16)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    startLine: 44
                                                )
                                                eligible: true
                                                endLine: 44
                                                functionName: concreteMethod
                                                functionScope: nodeId(14)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                nodeId: 16
                                                origNode: nodeId(16)
                                                parent: nodeId(14)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                startLine: 44
                                            )
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            endLine: 44
                                            functionName: concreteMethod
                                            functionScope: nodeId(14)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            nodeId: 19
                                            origNode: nodeId(19)
                                            parent: nodeId(14)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            startLine: 44
                                        )
                                        stmts: array(
                                            0: Stmt_If(
                                                cond: Expr_BinaryOp_Identical(
                                                    left: Expr_Variable(
                                                        eligible: true
                                                        endLine: 46
                                                        functionName: concreteMethod
                                                        functionScope: nodeId(14)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        mutationCandidate: true
                                                        nodeId: 22
                                                        origNode: nodeId(22)
                                                        parent: nodeId(21)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        startLine: 46
                                                    )
                                                    right: Expr_ConstFetch(
                                                        name: Name(
                                                            eligible: true
                                                            endLine: 46
                                                            functionName: concreteMethod
                                                            functionScope: nodeId(14)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            mutationCandidate: true
                                                            namespacedName: FullyQualified(Infection\Tests\PhpParser\Visitor\EnrichmentTraverse\Fixtures\null)
                                                            nodeId: 24
                                                            origNode: nodeId(24)
                                                            parent: nodeId(23)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            startLine: 46
                                                        )
                                                        eligible: true
                                                        endLine: 46
                                                        functionName: concreteMethod
                                                        functionScope: nodeId(14)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        mutationCandidate: true
                                                        nodeId: 23
                                                        origNode: nodeId(23)
                                                        parent: nodeId(21)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        startLine: 46
                                                    )
                                                    eligible: true
                                                    endLine: 46
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(14)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 21
                                                    origNode: nodeId(21)
                                                    parent: nodeId(20)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    startLine: 46
                                                )
                                                stmts: array(
                                                    0: Stmt_Echo(
                                                        exprs: array(
                                                            0: Scalar_String(
                                                                eligible: true
                                                                endLine: 47
                                                                functionName: concreteMethod
                                                                functionScope: nodeId(14)
                                                                isInsideFunction: true
                                                                isStrictTypes: true
                                                                kind: KIND_SINGLE_QUOTED (1)
                                                                nodeId: 26
                                                                origNode: nodeId(26)
                                                                parent: nodeId(25)
                                                                rawValue: 'nothing to do'
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                startLine: 47
                                                            )
                                                        )
                                                        eligible: true
                                                        endLine: 47
                                                        functionName: concreteMethod
                                                        functionScope: nodeId(14)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        next: nodeId(27)
                                                        nodeId: 25
                                                        origNode: nodeId(25)
                                                        parent: nodeId(20)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        startLine: 47
                                                    )
                                                )
                                                else: Stmt_Else(
                                                    stmts: array(
                                                        0: Stmt_Expression(
                                                            expr: Expr_FuncCall(
                                                                name: Expr_Variable(
                                                                    eligible: true
                                                                    endLine: 49
                                                                    functionName: concreteMethod
                                                                    functionScope: nodeId(14)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: true
                                                                    nodeId: 30
                                                                    origNode: nodeId(30)
                                                                    parent: nodeId(29)
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    startLine: 49
                                                                )
                                                                eligible: true
                                                                endLine: 49
                                                                functionName: concreteMethod
                                                                functionScope: nodeId(14)
                                                                isInsideFunction: true
                                                                isStrictTypes: true
                                                                nodeId: 29
                                                                origNode: nodeId(29)
                                                                parent: nodeId(28)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                startLine: 49
                                                            )
                                                            eligible: true
                                                            endLine: 49
                                                            functionName: concreteMethod
                                                            functionScope: nodeId(14)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            nodeId: 28
                                                            origNode: nodeId(28)
                                                            parent: nodeId(27)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            startLine: 49
                                                        )
                                                    )
                                                    eligible: true
                                                    endLine: 50
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(14)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    next: nodeId(28)
                                                    nodeId: 27
                                                    origNode: nodeId(27)
                                                    parent: nodeId(20)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    startLine: 48
                                                )
                                                eligible: true
                                                endLine: 50
                                                functionName: concreteMethod
                                                functionScope: nodeId(14)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                next: nodeId(25)
                                                nodeId: 20
                                                origNode: nodeId(20)
                                                parent: nodeId(14)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                startLine: 46
                                            )
                                        )
                                        eligible: true
                                        endLine: 51
                                        functionName: concreteMethod
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        mutationCandidate: true
                                        nodeId: 14
                                        origNode: nodeId(14)
                                        parent: nodeId(6)
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        startLine: 44
                                    )
                                    3: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            endLine: 53
                                            functionName: abstractMethod
                                            functionScope: nodeId(31)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            nodeId: 32
                                            origNode: nodeId(32)
                                            parent: nodeId(31)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            startLine: 53
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    eligible: true
                                                    endLine: 53
                                                    functionName: abstractMethod
                                                    functionScope: nodeId(31)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    nodeId: 34
                                                    origNode: nodeId(34)
                                                    parent: nodeId(33)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    startLine: 53
                                                )
                                                var: Expr_Variable(
                                                    eligible: true
                                                    endLine: 53
                                                    functionName: abstractMethod
                                                    functionScope: nodeId(31)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    nodeId: 35
                                                    origNode: nodeId(35)
                                                    parent: nodeId(33)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    startLine: 53
                                                )
                                                eligible: true
                                                endLine: 53
                                                functionName: abstractMethod
                                                functionScope: nodeId(31)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                nodeId: 33
                                                origNode: nodeId(33)
                                                parent: nodeId(31)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                startLine: 53
                                            )
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            endLine: 53
                                            functionName: abstractMethod
                                            functionScope: nodeId(31)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            nodeId: 36
                                            origNode: nodeId(36)
                                            parent: nodeId(31)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            startLine: 53
                                        )
                                        eligible: true
                                        endLine: 55
                                        functionName: abstractMethod
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        nodeId: 31
                                        origNode: nodeId(31)
                                        parent: nodeId(6)
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        startLine: 53
                                    )
                                )
                                eligible: true
                                endLine: 56
                                next: nodeId(8)
                                nodeId: 6
                                origNode: nodeId(6)
                                parent: nodeId(4)
                                startLine: 38
                            )
                        )
                        eligible: true
                        endLine: 56
                        kind: 1
                        next: nodeId(6)
                        nodeId: 4
                        origNode: nodeId(4)
                        startLine: 36
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
                                    eligible: true
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    eligible: true
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                    rawValue: 1
                                )
                                eligible: true
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                            )
                        )
                        eligible: true
                        next: nodeId(4)
                        nodeId: 0
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: true
                                    nodeId: 7
                                    origNode: nodeId(7)
                                    parent: nodeId(6)
                                )
                                stmts: array(
                                    0: Stmt_TraitUse(
                                        traits: array(
                                            0: Name(
                                                eligible: true
                                                nodeId: 9
                                                origNode: nodeId(9)
                                                parent: nodeId(8)
                                                resolvedName: FullyQualified(Infection\Tests\PhpParser\Visitor\EnrichmentTraverse\Fixtures\TraitExample)
                                            )
                                        )
                                        eligible: true
                                        next: nodeId(10)
                                        nodeId: 8
                                        origNode: nodeId(8)
                                        parent: nodeId(6)
                                    )
                                    1: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    eligible: true
                                                    nodeId: 12
                                                    origNode: nodeId(12)
                                                    parent: nodeId(11)
                                                )
                                                value: Scalar_String(
                                                    eligible: true
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    nodeId: 13
                                                    origNode: nodeId(13)
                                                    parent: nodeId(11)
                                                    rawValue: ''
                                                )
                                                eligible: true
                                                nodeId: 11
                                                origNode: nodeId(11)
                                                parent: nodeId(10)
                                            )
                                        )
                                        eligible: true
                                        nodeId: 10
                                        origNode: nodeId(10)
                                        parent: nodeId(6)
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
                                        eligible: true
                                        nodeId: 14
                                        origNode: nodeId(14)
                                    )
                                    3: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            functionName: concreteMethod
                                            functionScope: nodeId(20)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 21
                                            origNode: nodeId(21)
                                            parent: nodeId(20)
                                            reflectionClass: Infection\Reflection\NullReflection
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    eligible: true
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(20)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 23
                                                    origNode: nodeId(23)
                                                    parent: nodeId(22)
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                )
                                                var: Expr_Variable(
                                                    eligible: true
                                                    functionName: concreteMethod
                                                    functionScope: nodeId(20)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 24
                                                    origNode: nodeId(24)
                                                    parent: nodeId(22)
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                )
                                                eligible: true
                                                functionName: concreteMethod
                                                functionScope: nodeId(20)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                nodeId: 22
                                                origNode: nodeId(22)
                                                parent: nodeId(20)
                                                reflectionClass: Infection\Reflection\NullReflection
                                            )
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            functionName: concreteMethod
                                            functionScope: nodeId(20)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 25
                                            origNode: nodeId(25)
                                            parent: nodeId(20)
                                            reflectionClass: Infection\Reflection\NullReflection
                                        )
                                        eligible: true
                                        functionName: concreteMethod
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        mutationCandidate: true
                                        nodeId: 20
                                        origNode: nodeId(20)
                                        parent: nodeId(6)
                                        reflectionClass: Infection\Reflection\NullReflection
                                    )
                                )
                                eligible: true
                                next: nodeId(8)
                                nodeId: 6
                                origNode: nodeId(6)
                                parent: nodeId(4)
                            )
                        )
                        eligible: true
                        kind: 1
                        next: nodeId(6)
                        nodeId: 4
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        // Incorrect as in: used on a node that is not a mutation candidate
        yield 'with incorrect usage infection-ignore-all' => [
            file_get_contents(self::FIXTURES_DIR . '/ProblematicIgnoreAll.php'),
            null,
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    eligible: true
                                    nodeId: 2
                                    origNode: nodeId(2)
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    eligible: true
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    origNode: nodeId(3)
                                    parent: nodeId(1)
                                    rawValue: 1
                                )
                                eligible: true
                                nodeId: 1
                                origNode: nodeId(1)
                                parent: nodeId(0)
                            )
                        )
                        eligible: true
                        next: nodeId(4)
                        nodeId: 0
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 5
                            origNode: nodeId(5)
                            parent: nodeId(4)
                        )
                        stmts: array(
                            0: Stmt_Use(
                                uses: array(
                                    0: UseItem(
                                        name: Name(
                                            eligible: true
                                            nodeId: 8
                                            origNode: nodeId(8)
                                            parent: nodeId(7)
                                        )
                                        alias: Identifier(
                                            eligible: true
                                            nodeId: 9
                                            origNode: nodeId(9)
                                            parent: nodeId(7)
                                        )
                                        eligible: true
                                        nodeId: 7
                                        origNode: nodeId(7)
                                        parent: nodeId(6)
                                    )
                                )
                                eligible: true
                                next: nodeId(10)
                                nodeId: 6
                                origNode: nodeId(6)
                                parent: nodeId(4)
                            )
                            1: Stmt_Use(
                                uses: array(
                                    0: UseItem(
                                        name: Name(
                                            eligible: false
                                            nodeId: 12
                                            origNode: nodeId(12)
                                            parent: nodeId(11)
                                        )
                                        alias: Identifier(
                                            eligible: false
                                            nodeId: 13
                                            origNode: nodeId(13)
                                            parent: nodeId(11)
                                        )
                                        eligible: false
                                        nodeId: 11
                                        origNode: nodeId(11)
                                        parent: nodeId(10)
                                    )
                                )
                                eligible: false
                                next: nodeId(14)
                                nodeId: 10
                                origNode: nodeId(10)
                                parent: nodeId(4)
                            )
                            2: Stmt_Class(
                                name: Identifier(
                                    eligible: true
                                    nodeId: 15
                                    origNode: nodeId(15)
                                    parent: nodeId(14)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                            functionName: check
                                            functionScope: nodeId(16)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 17
                                            origNode: nodeId(17)
                                            parent: nodeId(16)
                                            reflectionClass: Infection\Reflection\NullReflection
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    eligible: true
                                                    functionName: check
                                                    functionScope: nodeId(16)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 19
                                                    origNode: nodeId(19)
                                                    parent: nodeId(18)
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                )
                                                var: Expr_Variable(
                                                    eligible: true
                                                    functionName: check
                                                    functionScope: nodeId(16)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 20
                                                    origNode: nodeId(20)
                                                    parent: nodeId(18)
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                )
                                                eligible: true
                                                functionName: check
                                                functionScope: nodeId(16)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                nodeId: 18
                                                origNode: nodeId(18)
                                                parent: nodeId(16)
                                                reflectionClass: Infection\Reflection\NullReflection
                                            )
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                            functionName: check
                                            functionScope: nodeId(16)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            mutationCandidate: true
                                            nodeId: 21
                                            origNode: nodeId(21)
                                            parent: nodeId(16)
                                            reflectionClass: Infection\Reflection\NullReflection
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_BooleanOr(
                                                    left: Expr_Instanceof(
                                                        expr: Expr_Variable(
                                                            eligible: true
                                                            functionName: check
                                                            functionScope: nodeId(16)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            mutationCandidate: true
                                                            nodeId: 25
                                                            origNode: nodeId(25)
                                                            parent: nodeId(24)
                                                            reflectionClass: Infection\Reflection\NullReflection
                                                        )
                                                        class: Name(
                                                            eligible: true
                                                            functionName: check
                                                            functionScope: nodeId(16)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            mutationCandidate: true
                                                            nodeId: 26
                                                            origNode: nodeId(26)
                                                            parent: nodeId(24)
                                                            reflectionClass: Infection\Reflection\NullReflection
                                                            resolvedName: FullyQualified(RuntimeException)
                                                        )
                                                        eligible: true
                                                        functionName: check
                                                        functionScope: nodeId(16)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        mutationCandidate: true
                                                        nodeId: 24
                                                        origNode: nodeId(24)
                                                        parent: nodeId(23)
                                                        reflectionClass: Infection\Reflection\NullReflection
                                                    )
                                                    right: Expr_Instanceof(
                                                        expr: Expr_Variable(
                                                            eligible: true
                                                            functionName: check
                                                            functionScope: nodeId(16)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            mutationCandidate: true
                                                            nodeId: 28
                                                            origNode: nodeId(28)
                                                            parent: nodeId(27)
                                                            reflectionClass: Infection\Reflection\NullReflection
                                                        )
                                                        class: Name(
                                                            eligible: true
                                                            functionName: check
                                                            functionScope: nodeId(16)
                                                            isInsideFunction: true
                                                            isStrictTypes: true
                                                            mutationCandidate: true
                                                            nodeId: 29
                                                            origNode: nodeId(29)
                                                            parent: nodeId(27)
                                                            reflectionClass: Infection\Reflection\NullReflection
                                                            resolvedName: FullyQualified(InvalidArgumentException)
                                                        )
                                                        eligible: true
                                                        functionName: check
                                                        functionScope: nodeId(16)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        mutationCandidate: true
                                                        nodeId: 27
                                                        origNode: nodeId(27)
                                                        parent: nodeId(23)
                                                        reflectionClass: Infection\Reflection\NullReflection
                                                    )
                                                    eligible: true
                                                    functionName: check
                                                    functionScope: nodeId(16)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    mutationCandidate: true
                                                    nodeId: 23
                                                    origNode: nodeId(23)
                                                    parent: nodeId(22)
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                )
                                                eligible: true
                                                functionName: check
                                                functionScope: nodeId(16)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                mutationCandidate: true
                                                nodeId: 22
                                                origNode: nodeId(22)
                                                parent: nodeId(16)
                                                reflectionClass: Infection\Reflection\NullReflection
                                            )
                                        )
                                        eligible: true
                                        functionName: check
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        mutationCandidate: true
                                        nodeId: 16
                                        origNode: nodeId(16)
                                        parent: nodeId(14)
                                        reflectionClass: Infection\Reflection\NullReflection
                                    )
                                )
                                eligible: true
                                nodeId: 14
                                origNode: nodeId(14)
                                parent: nodeId(4)
                            )
                        )
                        eligible: true
                        kind: 1
                        next: nodeId(6)
                        nodeId: 4
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];
    }
}
