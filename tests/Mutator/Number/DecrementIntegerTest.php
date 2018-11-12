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

namespace Infection\Tests\Mutator\Number;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class DecrementIntegerTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): array
    {
        return [
            'It decrements an integer' => [
                <<<'PHP'
<?php

if ($foo < 10) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < 9) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement the number one' => [
                <<<'PHP'
<?php

if ($foo < 1) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is being compared as identical with result of count()' => [
                <<<'PHP'
<?php

if (count($a) === 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero with yoda style when it is being compared as identical with result of count()' => [
                <<<'PHP'
<?php

if (0 === count($a)) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero with when it is being compared as identical with result of cOunT()' => [
                <<<'PHP'
<?php

if (cOunT($a) === 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is being compared as identical with result of sizeOf()' => [
                <<<'PHP'
<?php

if (sizeOf($a) === 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is being compared as not identical with result of count()' => [
                <<<'PHP'
<?php

if (count($a) !== 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is compared as equal with result of count()' => [
                <<<'PHP'
<?php

if (count($a) == 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is compared as not equal with result of count()' => [
                <<<'PHP'
<?php

if (count($a) != 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is compared as more than count()' => [
                <<<'PHP'
<?php

if (count($a) > 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is compared as less than count() on the right side' => [
                <<<'PHP'
<?php

if (0 < count($a)) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is compared as less than or equal to count() on the right side' => [
                <<<'PHP'
<?php

if (0 <= count($a)) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement zero when it is compared as equal to count() on the right side' => [
                <<<'PHP'
<?php

if (0 == count($a)) {
    echo 'bar';
}
PHP
                ,
            ],

            'It does not decrement zero when it is compared as greater than count() on the right side' => [
                <<<'PHP'
<?php

if (0 > count($a)) {
    echo 'bar';
}
PHP
            ],
            'It does not decrement zero when it is compared as more or equal than count()' => [
                <<<'PHP'
<?php

if (count($a) >= 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It doest not decrement zero when it is compared as less than count()' => [
                <<<'PHP'
<?php

if (count($a) < 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does decrement when compared against a variable function' => [
                <<<'PHP'
<?php

if ($foo < 0) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < -1) {
    echo 'bar';
}
PHP
                ,
            ],
            'It decrements zero when it is compared any other, not count() function' => [
                <<<'PHP'
<?php

if (abs($a) === 0) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if (abs($a) === -1) {
    echo 'bar';
}
PHP
                ,
            ],
            'It doest not decrements zero when it is compared as less or equal than count()' => [
                <<<'PHP'
<?php

if (count($a) <= 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It decrements zero' => [
                <<<'PHP'
<?php

if ($foo < 0) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < -1) {
    echo 'bar';
}
PHP
                ,
            ],
            'It increments a negative integer' => [
                <<<'PHP'
<?php

if ($foo < -10) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < -9) {
    echo 'bar';
}
PHP
                ,
            ],
        ];
    }
}
