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

namespace Infection\Tests\Mutator\Unwrap;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class UnwrapArrayColumnTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates correctly when provided with an array' => [
            <<<'PHP'
<?php

$a = array_column([['foo' => 'bar']], 'foo');
PHP
            ,
            <<<'PHP'
<?php

$a = [['foo' => 'bar']];
PHP
        ];

        yield 'It mutates correctly when provided with a constant' => [
            <<<'PHP'
<?php

$a = array_column(\Class_With_Const::Const, 'foo');
PHP
            ,
            <<<'PHP'
<?php

$a = \Class_With_Const::Const;
PHP
        ];

        yield 'It mutates correctly when a backslash is in front of array_column' => [
            <<<'PHP'
<?php

$a = \array_column([['foo' => 'bar']], 'foo');
PHP
            ,
            <<<'PHP'
<?php

$a = [['foo' => 'bar']];
PHP
        ];

        yield 'It does not mutate other array_ calls' => [
            <<<'PHP'
<?php

$a = array_map('strtolower', ['foo' => 'bar']);
PHP
        ];

        yield 'It does not mutate functions named array_column' => [
            <<<'PHP'
<?php

function array_column($array, $columnKey, $indexKey = null)
{
}
PHP
        ];

        yield 'It mutates correctly within if statements' => [
            <<<'PHP'
<?php

$a = [['foo' => 'bar']];
if (array_column($a) === $a) {
    return true;
}
PHP
            ,
            <<<'PHP'
<?php

$a = [['foo' => 'bar']];
if ($a === $a) {
    return true;
}
PHP
        ];

        yield 'It mutates correctly when array_column is wrongly capitalized' => [
            <<<'PHP'
<?php

$a = aRrAy_CoLuMn([['foo' => 'bar']], 'foo');
PHP
            ,
            <<<'PHP'
<?php

$a = [['foo' => 'bar']];
PHP
        ];

        yield 'It mutates correctly when array_column uses another function as input' => [
            <<<'PHP'
<?php

$a = array_column($foo->bar(), 'foo');
PHP
            ,
            <<<'PHP'
<?php

$a = $foo->bar();
PHP
        ];

        yield 'It mutates correctly when provided with a more complex situation' => [
            <<<'PHP'
<?php

$a = array_map('strtolower', array_column([['foo' => 'bar']], 'foo'));
PHP
            ,
            <<<'PHP'
<?php

$a = array_map('strtolower', [['foo' => 'bar']]);
PHP
        ];

        yield 'It mutates correctly when the $indexKey parameter is present' => [
            <<<'PHP'
<?php

$a = array_column([['foo' => 'bar']], 'foo', $indexKey);
PHP
            ,
            <<<'PHP'
<?php

$a = [['foo' => 'bar']];
PHP
        ];

        yield 'It does not break when provided with a variable function name' => [
            <<<'PHP'
<?php

$a = 'array_column';

$b = $a([['foo' => 'bar']], 'foo');
PHP
            ,
        ];
    }
}
