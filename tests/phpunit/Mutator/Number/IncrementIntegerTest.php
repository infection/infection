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

namespace Infection\Tests\Mutator\Number;

use Infection\Tests\Mutator\BaseMutatorTestCase;

final class IncrementIntegerTest extends BaseMutatorTestCase
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
        yield 'It increments an integer' => [
            <<<'PHP'
<?php

if ($foo === 10) {
    echo 'bar';
}
PHP
            ,
            <<<'PHP'
<?php

if ($foo === 11) {
    echo 'bar';
}
PHP
            ,
        ];

        yield 'It does not increment the number zero' => [
            <<<'PHP'
<?php

if ($foo < 0) {
    echo 'bar';
}
PHP
            ,
        ];

        yield 'It increments one' => [
            <<<'PHP'
<?php

if ($foo === 1) {
    echo 'bar';
}
PHP
            ,
            <<<'PHP'
<?php

if ($foo === 2) {
    echo 'bar';
}
PHP
            ,
        ];

        yield 'It does not increment floats' => [
            <<<'PHP'
<?php

if ($foo === 1.0) {
    echo 'bar';
}
PHP
        ];

        yield 'It increments a negative integer' => [
            <<<'PHP'
<?php

if ($foo === -10) {
    echo 'bar';
}
PHP
            ,
            <<<'PHP'
<?php

if ($foo === -9) {
    echo 'bar';
}
PHP
            ,
        ];
    }
}
