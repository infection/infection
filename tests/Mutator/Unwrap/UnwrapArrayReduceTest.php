<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

/**
 * @internal
 */
final class UnwrapArrayReduceTest extends AbstractMutatorTestCase
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
        yield 'It mutates correctly when the $initial parameter is provided as an array' => [
            <<<'PHP'
<?php

$a = array_reduce(
    ['A', 1, 'C'],
    function ($carry, $item) {
       return $item;
    }, 
    ['D']
);
PHP
            ,
            <<<'PHP'
<?php

$a = ['D'];
PHP
        ];

        yield 'It mutates correctly when the $initial parameter is provided as a constant' => [
            <<<'PHP'
<?php

$a = array_reduce(
    ['A', 1, 'C'],
    function ($carry, $item) {
       return $item;
    }, 
    \Class_With_Const::Const
);
PHP
            ,
            <<<'PHP'
<?php

$a = \Class_With_Const::Const;
PHP
        ];

        yield 'It mutates correctly when the $initial parameter is provided and a backslash is in front of array_reduce' => [
            <<<'PHP'
<?php

$a = \array_reduce(
    ['A', 1, 'C'],
    function ($carry, $item) {
       return $item;
    },  
    ['D']
);
PHP
            ,
            <<<'PHP'
<?php

$a = ['D'];
PHP
        ];

        yield 'It mutates correctly within if statements when the $initial parameter is provided' => [
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
if (array_reduce($a, function ($carry, $item) { return $item; }, ['D']) === $a) {
    return true;
}
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
if (['D'] === $a) {
    return true;
}
PHP
        ];

        yield 'It mutates correctly when the $initial parameter is provided and array_reduce is wrongly capitalized' => [
            <<<'PHP'
<?php

$a = aRrAy_ReDuCe(
    ['A', 1, 'C'],
    function ($carry, $item) { 
        return $item;
    }, 
    ['D']
);
PHP
            ,
            <<<'PHP'
<?php

$a = ['D'];
PHP
        ];

        yield 'It mutates correctly when the $initial parameter is provided and array_reduce uses other functions as input' => [
            <<<'PHP'
<?php

$a = array_reduce(
    $foo->bar(), 
    $foo->baz(), 
    $foo->qux()
);
PHP
            ,
            <<<'PHP'
<?php

$a = $foo->qux();
PHP
        ];

        yield 'It mutates correctly when the $initial parameter is provided in a more complex situation' => [
            <<<'PHP'
<?php

$a = array_map('strtolower', array_reduce(['A', 1, 'C'], $callback, ['D']));
PHP
            ,
            <<<'PHP'
<?php

$a = array_map('strtolower', ['D']);
PHP
        ];

        yield 'It mutates correctly when the the $initial parameter is provided and the $callback parameter is provided as a variable' => [
            <<<'PHP'
<?php

$a = array_reduce(['A', 1, 'C'], $callback, ['D']);
PHP
            ,
            <<<'PHP'
<?php

$a = ['D'];
PHP
        ];

        yield 'It does not mutate when the $initial parameter is not provided' => [
            <<<'PHP'
<?php

$a = array_reduce(['A', 1, 'C'], function ($carry, $item) {
    return $item;
});
PHP
        ];

        yield 'It does not mutate other array_ calls' => [
            <<<'PHP'
<?php

$a = array_map('strtolower', ['A', 'B', 'C']);
PHP
        ];

        yield 'It does not mutate functions named array_reduce' => [
            <<<'PHP'
<?php

function array_reduce($array, $callback, $initial = null)
{
}
PHP
        ];
    }
}
