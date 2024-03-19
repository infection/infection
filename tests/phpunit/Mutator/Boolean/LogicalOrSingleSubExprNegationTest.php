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

use Infection\Tests\Mutator\BaseMutatorTestCase;

final class LogicalOrSingleSubExprNegationTest extends BaseMutatorTestCase
{
    /**
     * @dataProvider mutationsProvider
     *
     * @param string|string[] $expected
     */
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->doTest($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It mutates array item fetch' => [
            <<<'PHP'
<?php

$var = $array[0] || $array[1];
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !$array[0] || $array[1];
PHP,
                <<<'PHP'
<?php

$var = $array[0] || !$array[1];
PHP,
            ],
        ];

        yield 'It mutates variable' => [
            <<<'PHP'
<?php

$var = $foo || $bar;
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !$foo || $bar;
PHP,
                <<<'PHP'
<?php

$var = $foo || !$bar;
PHP,
            ],
        ];

        yield 'It mutates method call' => [
            <<<'PHP'
<?php

$var = $this->foo() || $bar->baz();
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !$this->foo() || $bar->baz();
PHP,
                <<<'PHP'
<?php

$var = $this->foo() || !$bar->baz();
PHP,
            ],
        ];

        yield 'It mutates static calls' => [
            <<<'PHP'
<?php

$var = self::foo() || static::bar() || Test::baz();
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !self::foo() || static::bar() || Test::baz();
PHP
                ,
                <<<'PHP'
<?php

$var = self::foo() || !static::bar() || Test::baz();
PHP
                ,
                <<<'PHP'
<?php

$var = self::foo() || static::bar() || !Test::baz();
PHP,
            ],
        ];

        yield 'It mutates constant calls' => [
            <<<'PHP'
<?php

$var = self::FOO || self::BAR;
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !self::FOO || self::BAR;
PHP
                ,
                <<<'PHP'
<?php

$var = self::FOO || !self::BAR;
PHP,
            ],
        ];

        yield 'It mutates closure calls' => [
            <<<'PHP'
<?php

$var = $foo() || $bar();
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !$foo() || $bar();
PHP
                ,
                <<<'PHP'
<?php

$var = $foo() || !$bar();
PHP,
            ],
        ];

        yield 'It mutates invoke calls' => [
            <<<'PHP'
<?php

$var = ($this->foo)() || ($this->bar)();
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !($this->foo)() || ($this->bar)();
PHP
                ,
                <<<'PHP'
<?php

$var = ($this->foo)() || !($this->bar)();
PHP,
            ],
        ];

        yield 'It mutates function calls' => [
            <<<'PHP'
<?php

$var = a() || b();
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !a() || b();
PHP,
                <<<'PHP'
<?php

$var = a() || !b();
PHP,
            ],
        ];

        yield 'It mutates and with more expressions' => [
            <<<'PHP'
<?php

$var = a() || b() || c() || d();
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !a() || b() || c() || d();
PHP
                ,
                <<<'PHP'
<?php

$var = a() || !b() || c() || d();
PHP
                ,
                <<<'PHP'
<?php

$var = a() || b() || !c() || d();
PHP
                ,
                <<<'PHP'
<?php

$var = a() || b() || c() || !d();
PHP,
            ],
        ];

        yield 'It does not mutate equal\'s expressions' => [
            <<<'PHP'
<?php

$var = a() != 1 || b() == 1;
PHP,
        ];

        yield 'It does not mutate identical\'s expressions' => [
            <<<'PHP'
<?php

$var = a() !== 1 || b() === 1;
PHP,
        ];

        yield 'It does not mutate already negated expressions' => [
            <<<'PHP'
<?php

$var = !(a() || !b());
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !(!a() || !b());
PHP
                ,
            ],
        ];

        yield 'It mutates expressions with logical and' => [
            <<<'PHP'
<?php

$var = a() || b() && c();
PHP
            ,
            [
                <<<'PHP'
<?php

$var = !a() || b() && c();
PHP,
            ],
        ];
    }
}
