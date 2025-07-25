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

use Infection\Mutator\Number\IncrementInteger;
use Infection\Testing\BaseMutatorTestCase;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(IncrementInteger::class)]
final class IncrementIntegerTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
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

        yield 'It does not increment assigment of 0' => [
            <<<'PHP'
                <?php

                $foo = 0;
                PHP
            ,
        ];

        yield 'It does not increment 0 in greater comparison' => [
            <<<'PHP'
                <?php

                if ($foo > 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not increment 0 in greater or equal comparison' => [
            <<<'PHP'
                <?php

                if ($foo >= 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not increment 0 in smaller comparison' => [
            <<<'PHP'
                <?php

                if ($foo < 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not increment 0 in smaller or equal comparison' => [
            <<<'PHP'
                <?php

                if ($foo <= 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not increment 0 in equal comparison' => [
            <<<'PHP'
                <?php

                if ($foo == 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not increment 0 in not equal comparison' => [
            <<<'PHP'
                <?php

                if ($foo != 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not increment 0 in identical comparison' => [
            <<<'PHP'
                <?php

                if ($foo === 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not increment 0 in not identical comparison' => [
            <<<'PHP'
                <?php

                if ($foo !== 0) {
                    echo 'bar';
                }
                PHP,
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
                PHP,
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

        yield 'It does not increment limit argument of preg_split function when it equals to -1' => [
            <<<'PHP'
                <?php
                preg_split('//', 'string', -1);
                PHP,
        ];

        yield 'It does increment limit argument of preg_split function when it equals to 0' => [
            <<<'PHP'
                <?php

                preg_split('//', 'string', 0);
                PHP
            ,
            <<<'PHP'
                <?php

                preg_split('//', 'string', 1);
                PHP,
        ];

        yield 'It does increment limit argument of preg_split function when it equals to -2' => [
            <<<'PHP'
                <?php

                preg_split('//', 'string', -2);
                PHP
            ,
            <<<'PHP'
                <?php

                preg_split('//', 'string', -1);
                PHP,
        ];

        $maxInt = PHP_INT_MAX;

        yield 'It does not increment max int' => [
            <<<"PHP"
                <?php

                random_int(10000000, {$maxInt});
                PHP
            ,
            <<<"PHP"
                <?php

                random_int(10000001, {$maxInt});
                PHP,
        ];

        $minIntPlus1 = PHP_INT_MIN + 1;
        $minIntPlus2 = $minIntPlus1 + 1;

        yield 'It increments min int plus one, up to value of -PHP_INT_MAX' => [
            <<<"PHP"
                <?php

                if (\$foo === {$minIntPlus1}) {
                    echo 'bar';
                }
                PHP
            ,
            <<<"PHP"
                <?php

                if (\$foo === {$minIntPlus2}) {
                    echo 'bar';
                }
                PHP
            ,
        ];

        yield 'It does not increment preg_match() return value above 1 on identical comparison' => [
            <<<"PHP"
                <?php

                if (preg_match() === 1) {}
                PHP,
        ];

        yield 'It does not increment preg_match() return value above 1 on equal comparison' => [
            <<<"PHP"
                <?php

                if (preg_match() == 1) {}
                PHP,
        ];

        yield 'It does not increment preg_match() return value above 1 on not-equal comparison' => [
            <<<"PHP"
                <?php

                if (preg_mAtch() != 1) {}
                PHP,
        ];

        yield 'It increments return value above 1 on not-equal comparison for userland function' => [
            <<<'PHP'
                <?php

                if (doFoo() != 1) {}
                PHP,
            <<<'PHP'
                <?php

                if (doFoo() != 2) {
                }
                PHP,
        ];

        yield 'It increments return value above 1 on not-equal comparison for dynamic function call' => [
            <<<'PHP'
                <?php

                $fn = 'doFoo';
                if ($fn() != 1) {}
                PHP,
            <<<'PHP'
                <?php

                $fn = 'doFoo';
                if ($fn() != 2) {
                }
                PHP,
        ];
    }
}
