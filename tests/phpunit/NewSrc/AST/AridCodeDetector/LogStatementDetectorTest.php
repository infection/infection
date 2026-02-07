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

namespace Infection\Tests\NewSrc\AST\AridCodeDetector;

use Infection\Tests\NewSrc\AST\AstTestCase;
use newSrc\AST\AridCodeDetector\LogStatementDetector;
use newSrc\AST\NodeVisitor\DetectAridCodeVisitor;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LogStatementDetector::class)]
final class LogStatementDetectorTest extends AstTestCase
{
    #[DataProvider('nodeProvider')]
    public function test_it_detects_arid_code(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->createParser()->parse($code);

        $traverser = new NodeTraverser(
            new DetectAridCodeVisitor(
                new LogStatementDetector(),
            ),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump(
            $nodes,
            onlyVisitedNodes: false,
        );

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'PHP native error log function' => [
            <<<'PHP'
                <?php

                error_log("Processing user: " . $user);

                PHP,
            <<<'OUT'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                ARID_CODE: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_BinaryOp_Concat(
                                        left: Scalar_String(
                                            kind: KIND_DOUBLE_QUOTED (2)
                                            rawValue: "Processing user: "
                                            ARID_CODE: 2
                                        )
                                        right: Expr_Variable(
                                            ARID_CODE: 2
                                        )
                                        ARID_CODE: 2
                                    )
                                    ARID_CODE: 2
                                )
                            )
                            ARID_CODE: 2
                        )
                    )
                )
                OUT,
        ];

        yield 'PHP native error log function (incorrect case)' => [
            <<<'PHP'
                <?php

                ERROR_LOG("Processing user: " . $user);

                PHP,
            <<<'OUT'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                ARID_CODE: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_BinaryOp_Concat(
                                        left: Scalar_String(
                                            kind: KIND_DOUBLE_QUOTED (2)
                                            rawValue: "Processing user: "
                                            ARID_CODE: 2
                                        )
                                        right: Expr_Variable(
                                            ARID_CODE: 2
                                        )
                                        ARID_CODE: 2
                                    )
                                    ARID_CODE: 2
                                )
                            )
                            ARID_CODE: 2
                        )
                    )
                )
                OUT,
        ];

        yield 'Laravel log facade' => [
            <<<'PHP'
                <?php

                use Illuminate\Support\Facades\Log;

                Log::info('This is an informational message.');
                Log::error('An error happened!');

                PHP,
            <<<'OUT'
                array(
                )
                OUT,
        ];

        yield '(PSR based) logger variable' => [
            <<<'PHP'
                <?php

                $logger->info('This is an informational message.');
                $logger->error('An error happened!');

                PHP,
            <<<'OUT'
                array(
                )
                OUT,
        ];

        yield '(PSR based) logger property' => [
            <<<'PHP'
                <?php

                use Psr\Log\LoggerInterface;

                class SomeClass {
                    function __construct(
                        private LoggerInterface $logger,
                    ) {}

                    function test() {
                        $this->logger->info('This is an informational message.');
                        $this->logger->error('An error happened!');
                    }
                }

                PHP,
            <<<'OUT'
                array(
                    0: Stmt_Use(
                        uses: array(
                            0: UseItem(
                                name: Name
                            )
                        )
                    )
                    1: Stmt_Class(
                        name: Identifier
                        stmts: array(
                            0: Stmt_ClassMethod(
                                name: Identifier
                                params: array(
                                    0: Param(
                                        type: Name
                                        var: Expr_Variable
                                    )
                                )
                            )
                            1: Stmt_ClassMethod(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_Expression(
                                        expr: Expr_MethodCall(
                                            var: Expr_PropertyFetch(
                                                var: Expr_Variable(
                                                    ARID_CODE: 2
                                                )
                                                name: Identifier(
                                                    ARID_CODE: 2
                                                )
                                                ARID_CODE: 2
                                            )
                                            name: Identifier(
                                                ARID_CODE: 2
                                            )
                                            args: array(
                                                0: Arg(
                                                    value: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'This is an informational message.'
                                                        ARID_CODE: 2
                                                    )
                                                    ARID_CODE: 2
                                                )
                                            )
                                            ARID_CODE: 2
                                        )
                                    )
                                    1: Stmt_Expression(
                                        expr: Expr_MethodCall(
                                            var: Expr_PropertyFetch(
                                                var: Expr_Variable(
                                                    ARID_CODE: 2
                                                )
                                                name: Identifier(
                                                    ARID_CODE: 2
                                                )
                                                ARID_CODE: 2
                                            )
                                            name: Identifier(
                                                ARID_CODE: 2
                                            )
                                            args: array(
                                                0: Arg(
                                                    value: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'An error happened!'
                                                        ARID_CODE: 2
                                                    )
                                                    ARID_CODE: 2
                                                )
                                            )
                                            ARID_CODE: 2
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
                OUT,
        ];

        yield 'log-alike method call' => [
            <<<'PHP'
                <?php

                $x->logInfo('This is an informational message.');
                $x->logMessage('An error happened!');

                PHP,
            <<<'OUT'
                array(
                )
                OUT,
        ];

        // TODO: A LOT more tests, there is more to capture and check that we do not capture.
        //  for example a custom logger should not be marked as arid, although its _usage_ can.
    }
}
