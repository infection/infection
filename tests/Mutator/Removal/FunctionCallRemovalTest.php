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

namespace Infection\Tests\Mutator\Removal;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class FunctionCallRemovalTest extends AbstractMutatorTestCase
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
        yield 'It removes a function call without parameters' => [
            <<<'PHP'
<?php

foo();
$a = 3;
PHP
            ,
            <<<'PHP'
<?php

$a = 3;
PHP
            ,
        ];

        yield 'It removes a function call with parameters' => [
            <<<'PHP'
<?php

bar(3, 4);
$a = 3;
PHP
            ,
            <<<'PHP'
<?php

$a = 3;
PHP
            ,
        ];

        yield 'It removes dynamic function calls with string' => [
            <<<'PHP'
<?php

$start = true;
('foo')();
$end = true;

PHP
            ,
            <<<'PHP'
<?php

$start = true;

$end = true;
PHP
            ,
        ];

        yield 'It removes dynamic function call with variable' => [
            <<<'PHP'
<?php

$start = true;
$foo();
$end = true;

PHP
            ,
            <<<'PHP'
<?php

$start = true;

$end = true;
PHP
            ,
        ];

        yield 'It does not remove a function call that is assigned to something' => [
            <<<'PHP'
<?php

$b = foo();
$a = 3;
PHP
            ,
        ];

        yield 'It does not remove a function call within a statement' => [
            <<<'PHP'
<?php

if (foo()) {
    $a = 3;
}
while (foo()) {
    $a = 3;
}

PHP
            ,
        ];

        yield 'It does not remove a function call that is the parameter of another function or method' => [
            <<<'PHP'
<?php

$a = foo(3, bar());
PHP
        ];

        yield 'It does not remove a method call' => [
            <<<'PHP'
<?php

$this->foo();
$a = 3;
PHP
        ];

        yield 'It does not remove an assert() call' => [
            <<<'PHP'
<?php

assert(true === true);
aSsert(true === true);
\assert(true === true);
$a = 3;
PHP
        ];
    }
}
