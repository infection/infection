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
use Infection\Tests\Mutator\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LogicalAndAllSubExprNegation::class)]
final class LogicalAndAllSubExprNegationTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->doTest($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It mutates and with two expressions' => [
            <<<'PHP'
                <?php

                $var = a() && b();
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $var = !a() && !b();
                    PHP,
            ],
        ];

        yield 'It mutates and with more expressions' => [
            <<<'PHP'
                <?php

                $var = a() && b() && c() && d();
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $var = !a() && !b() && !c() && !d();
                    PHP,
            ],
        ];

        yield 'It mutates already negated expressions' => [
            <<<'PHP'
                <?php

                $var = !(a() && !b());
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $var = !(!a() && b());
                    PHP,
            ],
        ];

        yield 'It mutates assignments in boolean expressions' => [
            <<<'PHP'
                <?php

                $var = ($a = 1) && $b;
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $var = !($a = 1) && !$b;
                    PHP,
            ],
        ];

        yield 'It mutates more complex expressions' => [
            <<<'PHP'
                <?php

                $var = $A > 1 && $this->foo() === false && self::bar() >= 10;
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $var = !($A > 1) && $this->foo() === false && !(self::bar() >= 10);
                    PHP
                ,
            ],
        ];

        yield 'It does not mutate if all are identical comparisons' => [
            <<<'PHP'
                <?php

                $var = $a === false && b() === false && $c !== false && d() !== true;
                PHP
            ,
        ];

        yield 'It does not mutate if all are identical comparisons - with first OR' => [
            <<<'PHP'
                <?php

                $var = $a === false || b() === false && $c !== false && d() !== true;
                PHP
            ,
        ];

        yield 'It does not mutate if all are identical comparisons - with second OR' => [
            <<<'PHP'
                <?php

                $var = $a === false && b() === false || $c !== false && d() !== true;
                PHP
            ,
        ];

        yield 'It does not mutate if all are identical comparisons - with third OR' => [
            <<<'PHP'
                <?php

                $var = $a === false && b() === false && $c !== false || d() !== true;
                PHP
            ,
        ];

        yield 'It mutates the only one mutable expression on the left when others are not mutable' => [
            <<<'PHP'
                <?php

                $var = $a && b() === false && $c !== false;
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $var = !$a && b() === false && $c !== false;
                    PHP
                ,
            ],
        ];

        yield 'It mutates the only one mutable expression in the middle when others are not mutable' => [
            <<<'PHP'
                <?php

                $var = $a === false && b() && $c !== false;
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $var = $a === false && !b() && $c !== false;
                    PHP
                ,
            ],
        ];

        yield 'It mutates the only one mutable expression on the right when others are not mutable' => [
            <<<'PHP'
                <?php

                $var = $a === false && b() === false && $c;
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $var = $a === false && b() === false && !$c;
                    PHP
                ,
            ],
        ];
    }
}
