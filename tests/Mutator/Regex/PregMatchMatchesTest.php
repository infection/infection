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

namespace Infection\Tests\Mutator\Regex;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class PregMatchMatchesTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider providesMutatorCases
     */
    public function test_mutator(string $input, string $output = null): void
    {
        $this->doTest($input, $output);
    }

    public function providesMutatorCases(): \Generator
    {
        yield 'It mutates ' => [
            <<<'PHP'
<?php

preg_match('/a/', 'b', $foo);
PHP
            ,
            <<<'PHP'
<?php

(int) ($foo = array());
PHP
        ];

        yield 'It does not mutate if the function is a variable' => [
            <<<'PHP'
<?php

$foo = 'preg_match';
$foo('/a/', 'b', $bar);
PHP
        ];

        yield 'It mutates if preg_match is incorrectly cased' => [
          <<<'PHP'
<?php

PreG_maTch('/a/', 'b', $foo);
PHP
            ,
            <<<'PHP'
<?php

(int) ($foo = array());
PHP
        ];

        yield 'It does not mutate if there are less than 3 arguments' => [
            <<<'PHP'
<?php

preg_match('/asdfa/', 'foo');
PHP
        ];

        yield 'It mutates correctly if the 3rd variable is a property' => [
            <<<'PHP'
<?php

preg_match('/a/', 'b', $a->b);
PHP
            ,
            <<<'PHP'
<?php

(int) ($a->b = array());
PHP
        ];

        yield 'It mutates correctly even with four arguments' => [
            <<<'PHP'
<?php

preg_match('/a/', 'b', $foo, PREG_OFFSET_CAPTURE);
PHP
            ,
            <<<'PHP'
<?php

(int) ($foo = array());
PHP
        ];

        yield 'It mutates correctly even with five arguments' => [
            <<<'PHP'
<?php

preg_match('/a/', 'b', $foo, PREG_OFFSET_CAPTURE, 3);
PHP
            ,
            <<<'PHP'
<?php

(int) ($foo = array());
PHP
        ];
    }
}
