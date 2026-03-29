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

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalAndAllSubExprNegation;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LogicalAndAllSubExprNegation::class)]
final class LogicalAndAllSubExprNegationTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It mutates negated instanceof with 1 concrete class and 1 trait' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = !$node instanceof Node\Expr\PostDec && !$node instanceof Infection\Tests\Mutant\MutantAssertions;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof Node\Expr\PostDec && $node instanceof Infection\Tests\Mutant\MutantAssertions;
                    PHP,
            ),
        ];

        yield 'It mutates negated instanceof with 1 concrete class and 1 interface' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = !$node instanceof \Countable && !$node instanceof Node\Expr\PostDec;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof \Countable && $node instanceof Node\Expr\PostDec;
                    PHP,
            ),
        ];

        yield 'It mutates negated instanceof with 2 concrete classes (different variables)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = !$node1 instanceof PhpParser\Node\Expr\PreDec && !$node2 instanceof PhpParser\Node\Expr\PostDec;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node1 instanceof PhpParser\Node\Expr\PreDec && $node2 instanceof PhpParser\Node\Expr\PostDec;
                    PHP,
            ),
        ];

        yield 'It does not mutate negated instanceof with 2 concrete classes (same variable)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = !$node instanceof PhpParser\Node\Expr\PreDec && !$node instanceof PhpParser\Node\Expr\PostDec;
                    PHP,
            ),
        ];

        yield 'It does not mutate negated instanceof with 3 concrete classes' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = !$node instanceof PhpParser\Node\Expr\PreDec && !$node instanceof PhpParser\Node\Expr\PostDec && !$node instanceof PhpParser\Node\Expr\BooleanNot;
                    PHP,
            ),
        ];

        yield 'It mutates and with two expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = a() && b();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !a() && !b();
                        PHP,
                ),
            ],
        ];

        yield 'It mutates and with more expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = a() && b() && c() && d();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !a() && !b() && !c() && !d();
                        PHP,
                ),
            ],
        ];

        yield 'It mutates already negated expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = !(a() && !b());
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !(!a() && b());
                        PHP,
                ),
            ],
        ];

        yield 'It mutates assignments in boolean expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = ($a = 1) && $b;
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !($a = 1) && !$b;
                        PHP,
                ),
            ],
        ];

        yield 'It mutates more complex expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $A > 1 && $this->foo() === false && self::bar() >= 10;
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !($A > 1) && $this->foo() === false && !(self::bar() >= 10);
                        PHP,
                ),
            ],
        ];

        yield 'It does not mutate if all are identical comparisons' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $a === false && b() === false && $c !== false && d() !== true;
                    PHP,
            ),
        ];

        yield 'It does not mutate if all are identical comparisons - with first OR' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $a === false || b() === false && $c !== false && d() !== true;
                    PHP,
            ),
        ];

        yield 'It does not mutate if all are identical comparisons - with second OR' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $a === false && b() === false || $c !== false && d() !== true;
                    PHP,
            ),
        ];

        yield 'It does not mutate if all are identical comparisons - with third OR' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $a === false && b() === false && $c !== false || d() !== true;
                    PHP,
            ),
        ];

        yield 'It mutates the only one mutable expression on the left when others are not mutable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $a && b() === false && $c !== false;
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !$a && b() === false && $c !== false;
                        PHP,
                ),
            ],
        ];

        yield 'It mutates the only one mutable expression in the middle when others are not mutable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $a === false && b() && $c !== false;
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = $a === false && !b() && $c !== false;
                        PHP,
                ),
            ],
        ];

        yield 'It mutates the only one mutable expression on the right when others are not mutable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $a === false && b() === false && $c;
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = $a === false && b() === false && !$c;
                        PHP,
                ),
            ],
        ];

        yield 'It preserves formatting for non-modified code' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    class TestFormatPreserving {
                        // some comment
                        public function test(): bool { // and comment here
                            return 1

                              || 2;
                        }
                    }

                    $var = a() && b();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        class TestFormatPreserving {
                            // some comment
                            public function test(): bool { // and comment here
                                return 1

                                  || 2;
                            }
                        }

                        $var = !a() && !b();
                        PHP,
                ),
            ],
        ];
    }
}
