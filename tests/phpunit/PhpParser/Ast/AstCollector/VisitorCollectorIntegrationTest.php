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

namespace Infection\Tests\PhpParser\Ast\AstCollector;

use Infection\Ast\AstCollector;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\TestFramework\Tracing\Trace\EmptyTrace;
use Infection\Testing\SingletonContainer;
use Infection\Tests\PhpParser\Ast\VisitorTestCase;
use Infection\Tests\PhpParser\Ast\Visitor\AddIdToTraversedNodesVisitor\MarkTraversedNodesVisitor;
use Infection\Tests\TestFramework\Tracing\DummyTracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Finder\SplFileInfo;
use function file_exists;
use function Safe\file_get_contents;

#[Group('integration')]
#[CoversClass(AstCollector::class)]
final class VisitorCollectorIntegrationTest extends VisitorTestCase
{
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    public function test_it_creates_a_rich_ast(): void
    {
        $fileInfoMock = $this->createSplFileInfoMock(self::FIXTURES_DIR . '/TwoAdditions.php');

        $astCollector = new AstCollector(
            SingletonContainer::getContainer()->getFileParser(),
            $this->createNodeTraverserFactory($fileInfoMock),
            new DummyTracer(),
            onlyCovered: false,
        );

        $ast = $astCollector->generate($fileInfoMock);

        $expected = <<<'AST'
            array(
                0: Stmt_Declare(
                    declares: array(
                        0: DeclareItem(
                            key: Identifier(
                                NOT_SUPPORTED: 5
                                TESTS: Deferred(array(
                                ))
                                NOT_COVERED_BY_TESTS: 0
                                ELIGIBLE: -1
                            )
                            value: Scalar_Int(
                                rawValue: 1
                                kind: KIND_DEC (10)
                                NOT_SUPPORTED: 5
                                TESTS: Deferred(array(
                                ))
                                NOT_COVERED_BY_TESTS: 0
                                ELIGIBLE: -1
                            )
                            NOT_SUPPORTED: 5
                            TESTS: Deferred(array(
                            ))
                            NOT_COVERED_BY_TESTS: 0
                            ELIGIBLE: -1
                        )
                    )
                    NOT_SUPPORTED: 5
                    TESTS: Deferred(array(
                    ))
                    NOT_COVERED_BY_TESTS: 0
                    ELIGIBLE: -1
                )
                1: Stmt_Namespace(
                    name: Name(
                        NOT_SUPPORTED: 5
                        TESTS: Deferred(array(
                        ))
                        NOT_COVERED_BY_TESTS: 0
                        ELIGIBLE: -1
                    )
                    stmts: array(
                        0: Stmt_Class(
                            name: Identifier(
                                NOT_SUPPORTED: 5
                                TESTS: Deferred(array(
                                ))
                                NOT_COVERED_BY_TESTS: 0
                                ELIGIBLE: -1
                            )
                            stmts: array(
                                0: Stmt_ClassMethod(
                                    name: Identifier(
                                        isInsideFunction: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: first
                                        TESTS: Deferred(array(
                                        ))
                                        NOT_COVERED_BY_TESTS: 0
                                        ELIGIBLE: -1
                                    )
                                    returnType: Identifier(
                                        isInsideFunction: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: first
                                        TESTS: Deferred(array(
                                        ))
                                        NOT_COVERED_BY_TESTS: 0
                                        ELIGIBLE: -1
                                    )
                                    stmts: array(
                                        0: Stmt_Return(
                                            expr: Expr_BinaryOp_Plus(
                                                left: Scalar_Int(
                                                    rawValue: 1
                                                    kind: KIND_DEC (10)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: first
                                                    TESTS: Deferred(array(
                                                    ))
                                                    NOT_COVERED_BY_TESTS: 0
                                                    ELIGIBLE: -1
                                                )
                                                right: Scalar_Int(
                                                    rawValue: 2
                                                    kind: KIND_DEC (10)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: first
                                                    TESTS: Deferred(array(
                                                    ))
                                                    NOT_COVERED_BY_TESTS: 0
                                                    ELIGIBLE: -1
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: first
                                                TESTS: Deferred(array(
                                                ))
                                                NOT_COVERED_BY_TESTS: 0
                                                ELIGIBLE: -1
                                            )
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: first
                                            TESTS: Deferred(array(
                                            ))
                                            NOT_COVERED_BY_TESTS: 0
                                            ELIGIBLE: -1
                                        )
                                    )
                                    isOnFunctionSignature: true
                                    isStrictTypes: true
                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                    functionName: first
                                    TESTS: Deferred(array(
                                    ))
                                    NOT_COVERED_BY_TESTS: 0
                                    ELIGIBLE: -1
                                )
                                1: Stmt_ClassMethod(
                                    name: Identifier(
                                        isInsideFunction: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: second
                                        TESTS: Deferred(array(
                                        ))
                                        NOT_COVERED_BY_TESTS: 0
                                        ELIGIBLE: -1
                                    )
                                    returnType: Identifier(
                                        isInsideFunction: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: second
                                        TESTS: Deferred(array(
                                        ))
                                        NOT_COVERED_BY_TESTS: 0
                                        ELIGIBLE: -1
                                    )
                                    stmts: array(
                                        0: Stmt_Return(
                                            expr: Expr_BinaryOp_Minus(
                                                left: Scalar_Int(
                                                    rawValue: 1
                                                    kind: KIND_DEC (10)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: second
                                                    TESTS: Deferred(array(
                                                    ))
                                                    NOT_COVERED_BY_TESTS: 0
                                                    ELIGIBLE: -1
                                                )
                                                right: Scalar_Int(
                                                    rawValue: 2
                                                    kind: KIND_DEC (10)
                                                    isInsideFunction: true
                                                    isStrictTypes: true
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: second
                                                    TESTS: Deferred(array(
                                                    ))
                                                    NOT_COVERED_BY_TESTS: 0
                                                    ELIGIBLE: -1
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: second
                                                TESTS: Deferred(array(
                                                ))
                                                NOT_COVERED_BY_TESTS: 0
                                                ELIGIBLE: -1
                                            )
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: second
                                            TESTS: Deferred(array(
                                            ))
                                            NOT_COVERED_BY_TESTS: 0
                                            ELIGIBLE: -1
                                        )
                                    )
                                    isOnFunctionSignature: true
                                    isStrictTypes: true
                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                    functionName: second
                                    TESTS: Deferred(array(
                                    ))
                                    NOT_COVERED_BY_TESTS: 0
                                    ELIGIBLE: -1
                                )
                            )
                            NOT_SUPPORTED: 5
                            TESTS: Deferred(array(
                            ))
                            NOT_COVERED_BY_TESTS: 0
                            ELIGIBLE: -1
                        )
                    )
                    kind: 1
                    NOT_SUPPORTED: 5
                    TESTS: Deferred(array(
                    ))
                    NOT_COVERED_BY_TESTS: 0
                    ELIGIBLE: -1
                )
            )
            AST;

        $actual = $this->dumper->dump($ast->nodes);

        $this->assertSame($expected, $actual);
    }

    private function createSplFileInfoMock(string $file): SplFileInfo&MockObject
    {
        $splFileInfoMock = $this->createMock(SplFileInfo::class);
        $splFileInfoMock->method('getRealPath')->willReturn($file);
        $splFileInfoMock->method('getContents')->willReturn(
            file_exists($file) ? file_get_contents($file) : 'content',
        );

        return $splFileInfoMock;
    }

    private function createNodeTraverserFactory(SplFileInfo $sourceFile): NodeTraverserFactory
    {
        $originalFactory = SingletonContainer::getContainer()->getNodeTraverserFactory();

        $traverser = $originalFactory->createFirstTraverser(
            new EmptyTrace($sourceFile),
        );
        $traverser->addVisitor(new MarkTraversedNodesVisitor());

        $factoryMock = $this->createMock(NodeTraverserFactory::class);
        $factoryMock
            ->method('createFirstTraverser')
            ->willReturn($traverser);

        return $factoryMock;
    }
}
