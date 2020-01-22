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

use Generator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

final class TrueValueTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider mutationsProvider
     *
     * @param string|string[] $expected
     */
    public function test_it_can_mutate($input, $expected = [], array $settings = []): void
    {
        $this->doTest($input, $expected, $settings);
    }

    public function mutationsProvider(): Generator
    {
        yield 'It mutates true to false' => [
                <<<'PHP'
<?php

return true;
PHP
                ,
                <<<'PHP'
<?php

return false;
PHP
                ,
            ];

        yield 'It mutates inside function call when function is a variable' => [
                <<<'PHP'
<?php

$a = 'foo';
$a(true);
PHP
                ,
                <<<'PHP'
<?php

$a = 'foo';
$a(false);
PHP
                ,
            ];

        yield 'It mutates inside function call when function is a string' => [
                <<<'PHP'
<?php

('function_name')(true);
PHP
                ,
                <<<'PHP'
<?php

('function_name')(false);
PHP
                ,
            ];

        yield 'It does not mutate the string true to false' => [
                <<<'PHP'
<?php

return 'true';
PHP
                ,
            ];

        yield 'It mutates all caps true to false' => [
                <<<'PHP'
<?php

return TRUE;
PHP
                ,
                <<<'PHP'
<?php

return false;
PHP
                ,
            ];

        yield 'It does not mutate when used in "in_array" function by default' => [
                <<<'PHP'
<?php

in_array($a, $b, true);
PHP
                ,
            ];

        yield 'It does not mutate when used in "\in_array" function by default' => [
                <<<'PHP'
<?php

\in_array($a, $b, true);
PHP
                ,
            ];

        yield 'It mutates when used in a method named "in_array"' => [
                <<<'PHP'
<?php

$a->in_array($b, $c, true);
PHP
                ,
            <<<'PHP'
<?php

$a->in_array($b, $c, false);
PHP
                ,
            ];

        yield 'It mutates when used in "\in_array" function and explicitly enabled in settings' => [
                <<<'PHP'
<?php

\in_array($a, $b, true);
PHP
                ,
            <<<'PHP'
<?php

\in_array($a, $b, false);
PHP
                ,
                ['in_array' => true],
            ];

        yield 'It does not mutate when used in "\in_array" function and explicitly disabled' => [
                <<<'PHP'
<?php

\in_array($a, $b, true);
PHP
                ,
                [],
                ['in_array' => false],
            ];

        yield 'It does not mutate when used in "array_search" function by default' => [
            <<<'PHP'
<?php

array_search($a, $b, true);
PHP
            ,
        ];

        yield 'It does not mutate when used in "\array_search" function by default' => [
            <<<'PHP'
<?php

\array_search($a, $b, true);
PHP
            ,
        ];

        yield 'It mutates when used in a method named "array_search"' => [
            <<<'PHP'
<?php

$a->array_search($b, $c, true);
PHP
            ,
            <<<'PHP'
<?php

$a->array_search($b, $c, false);
PHP
            ,
        ];

        yield 'It mutates when used in "array_search" function and explicitly enabled in settings' => [
            <<<'PHP'
<?php

array_search($a, $b, true);
PHP
            ,
            <<<'PHP'
<?php

array_search($a, $b, false);
PHP
            ,
            ['array_search' => true],
        ];

        yield 'It does not mutate when used in "\array_search" function and explicitly disabled' => [
            <<<'PHP'
<?php

\array_search($a, $b, true);
PHP
            ,
            [],
            ['array_search' => false],
        ];

        yield 'It does not mutate when used in "\array_search" function and explicitly disabled and function is wrongly capitalized' => [
            <<<'PHP'
<?php

\aRrAy_SeArCh($a, $b, true);
PHP
            ,
            [],
            ['array_search' => false],
        ];
    }

    public function test_mutates_true_value(): void
    {
        $falseValue = new ConstFetch(new Name('true'));

        $this->assertTrue($this->mutator->canMutate($falseValue));
    }

    public function test_does_not_mutate_false_value(): void
    {
        $trueValue = new ConstFetch(new Name('false'));

        $this->assertFalse($this->mutator->canMutate($trueValue));
    }
}
