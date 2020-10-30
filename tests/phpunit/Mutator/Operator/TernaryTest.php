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

final class TernaryTest extends BaseMutatorTestCase
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

    public function mutationsProvider(): iterable
    {
        yield 'Mutates ternary and flip conditions' => [
            <<<'PHP'
<?php

isset($b) ? 'B' : 'C';
PHP
            ,
            <<<'PHP'
<?php

isset($b) ? 'C' : 'B';
PHP
        ];

        yield 'Mutates ternary expression without values in the if condition' => [
            <<<'PHP'
<?php

$foo = 'foo';
$foo ?: 'bar';
PHP
            ,
            <<<'PHP'
<?php

$foo = 'foo';
$foo ? 'bar' : $foo;
PHP
        ];

        if (PHP_VERSION_ID < 80000) {
            yield 'Mutates nested ternary expression with values in the if condition' => [
                <<<'PHP'
<?php

true ? 'true' : false ? 't' : 'f';
PHP
                ,
                [
                    <<<'PHP'
<?php

true ? false : 'true' ? 't' : 'f';
PHP
                    ,
                    <<<'PHP'
<?php

true ? 'true' : false ? 'f' : 't';
PHP
                ],
            ];

            yield 'Mutates nested ternary expression without values in the if condition' => [
                <<<'PHP'
<?php

true ?: false ?: 'f';
PHP
                ,
                [
                    <<<'PHP'
<?php

true ? false : true ?: 'f';
PHP
                    ,
                    <<<'PHP'
<?php

true ?: false ? 'f' : (true ?: false);
PHP
                ],
            ];

            yield 'Mutates wrapped in braces ternary expressions with values in the if condition' => [
                <<<'PHP'
<?php

(true ? 'true' : false) ? 't' : 'f';
PHP
                ,
                [
                    <<<'PHP'
<?php

true ? false : 'true' ? 't' : 'f';
PHP
                    ,
                    <<<'PHP'
<?php

true ? 'true' : false ? 'f' : 't';
PHP
                ],
            ];

            yield 'Mutates wrapped in braces ternary expressions without values in the if condition' => [
                <<<'PHP'
<?php

((true ?: false) ? 't' : 'f');
PHP
                ,
                [
                    <<<'PHP'
<?php

true ? false : true ? 't' : 'f';
PHP
                    ,
                    <<<'PHP'
<?php

true ?: false ? 'f' : 't';
PHP
                ],
            ];
        }
    }
}
