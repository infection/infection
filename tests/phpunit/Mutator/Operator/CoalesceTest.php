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

namespace Infection\Tests\Mutator\Operator;

use Infection\Tests\Mutator\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CoalesceTest extends BaseMutatorTestCase
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
        yield 'Mutate coalesce and flip operands' => [
            <<<'PHP'
                <?php

                $foo = 'foo';
                $bar = 'bar';
                $foo ?? $bar;
                PHP
            ,
            <<<'PHP'
                <?php

                $foo = 'foo';
                $bar = 'bar';
                $bar ?? $foo;
                PHP,
        ];

        yield 'Mutate more than one coalesce operators and flip operands' => [
            <<<'PHP'
                <?php

                $foo = 'foo';
                $bar = 'bar';
                $baz = 'baz';
                $foo ?? $bar ?? $baz;
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $foo = 'foo';
                    $bar = 'bar';
                    $baz = 'baz';
                    $foo ?? $baz ?? $bar;
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $foo = 'foo';
                    $bar = 'bar';
                    $baz = 'baz';
                    $bar ?? $foo ?? $baz;
                    PHP,
            ],
        ];

        yield 'Mutate more than two coalesce operators and flip operands' => [
            <<<'PHP'
                <?php

                $foo = 'foo';
                $bar = 'bar';
                $baz = 'baz';
                $foo ?? $bar ?? $baz ?? 'oof';
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $foo = 'foo';
                    $bar = 'bar';
                    $baz = 'baz';
                    $foo ?? $bar ?? 'oof' ?? $baz;
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $foo = 'foo';
                    $bar = 'bar';
                    $baz = 'baz';
                    $foo ?? $baz ?? $bar ?? 'oof';
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $foo = 'foo';
                    $bar = 'bar';
                    $baz = 'baz';
                    $bar ?? $foo ?? $baz ?? 'oof';
                    PHP,
            ],
        ];

        yield 'It does not mutate when left operator is constant defined through `define` function' => [
            <<<'PHP'
                <?php

                define('FOO', 'foo');
                FOO ?? 'bar';
                PHP,
        ];

        yield 'It does not mutate when left operator is constant defined in class' => [
            <<<'PHP'
                <?php

                new class {
                    private const FOO = 'foo';

                    public function getFoo(): string
                    {
                        return self::FOO ?? 'bar';
                    }
                };
                PHP,
        ];

        yield 'It does not mutate when null is used with one coalesce' => [
            <<<'PHP'
                <?php

                $foo = 'foo';
                $foo ?? null;
                PHP
            ,
        ];

        yield 'It does not move null from the last position with 2 coalesce' => [
            <<<'PHP'
                <?php

                $foo = 'foo';
                $bar = 'bar';
                $foo ?? $bar ?? null;
                PHP
            ,
            <<<'PHP'
                <?php

                $foo = 'foo';
                $bar = 'bar';
                $bar ?? $foo ?? null;
                PHP,
        ];
    }
}
