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

use Infection\Mutator\Number\DecrementInteger;
use Infection\Tests\Mutator\MutatorTestCase;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function sprintf;

#[CoversClass(DecrementInteger::class)]
final class DecrementIntegerTest extends MutatorTestCase
{
    /**
     * @param string|string[]|null $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array|null $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        // @see https://github.com/infection/infection/pull/639
        yield 'It does not decrement an integer in a comparison to not overlap with GreaterThan and similar' => [
            <<<'PHP'
                <?php

                if ($foo < 10) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is being compared as identical with result of count()' => [
            <<<'PHP'
                <?php

                if (count($a) === 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero with yoda style when it is being compared as identical with result of count()' => [
            <<<'PHP'
                <?php

                if (0 === count($a)) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero with when it is being compared as identical with result of cOunT()' => [
            <<<'PHP'
                <?php

                if (cOunT($a) === 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is being compared as identical with result of sizeOf()' => [
            <<<'PHP'
                <?php

                if (sizeOf($a) === 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is being compared as not identical with result of count()' => [
            <<<'PHP'
                <?php

                if (count($a) !== 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is compared as equal with result of count()' => [
            <<<'PHP'
                <?php

                if (count($a) == 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is compared as not equal with result of count()' => [
            <<<'PHP'
                <?php

                if (count($a) != 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is compared as more than count()' => [
            <<<'PHP'
                <?php

                if (count($a) > 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is compared as less than count() on the right side' => [
            <<<'PHP'
                <?php

                if (0 < count($a)) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is compared as less than or equal to count() on the right side' => [
            <<<'PHP'
                <?php

                if (0 <= count($a)) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is compared as equal to count() on the right side' => [
            <<<'PHP'
                <?php

                if (0 == count($a)) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is compared as greater than count() on the right side' => [
            <<<'PHP'
                <?php

                if (0 > count($a)) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement zero when it is compared as more or equal than count()' => [
            <<<'PHP'
                <?php

                if (count($a) >= 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It doest not decrement zero when it is compared as less than count()' => [
            <<<'PHP'
                <?php

                if (count($a) < 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does decrement when compared against a variable function' => [
            <<<'PHP'
                <?php

                if ($foo === 0) {
                    echo 'bar';
                }
                PHP,
            <<<'PHP'
                <?php

                if ($foo === -1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It decrements zero when it is compared any other, not count() function' => [
            <<<'PHP'
                <?php

                if (abs($a) === 0) {
                    echo 'bar';
                }
                PHP,
            <<<'PHP'
                <?php

                if (abs($a) === -1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It doest not decrements zero when it is compared as less or equal than count()' => [
            <<<'PHP'
                <?php

                if (count($a) <= 0) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It decrements zero' => [
            <<<'PHP'
                <?php

                $a = 0;
                PHP,
            <<<'PHP'
                <?php

                $a = -1;
                PHP,
        ];

        yield 'It decrements a negative integer' => [
            <<<'PHP'
                <?php

                if ($foo === -10) {
                    echo 'bar';
                }
                PHP,
            <<<'PHP'
                <?php

                if ($foo === -11) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It decrements an assignment' => [
            <<<'PHP'
                <?php

                $foo = 10;
                PHP,
            <<<'PHP'
                <?php

                $foo = 9;
                PHP,
        ];

        yield 'It decrements an assignment of 0' => [
            <<<'PHP'
                <?php

                $foo = 0;
                PHP,
            <<<'PHP'
                <?php

                $foo = -1;
                PHP,
        ];

        yield 'It does not decrement an assignment of 1' => [
            <<<'PHP'
                <?php

                $foo = 1;
                PHP,
        ];

        yield 'It does not decrement 1 in greater comparison' => [
            <<<'PHP'
                <?php

                if ($foo > 1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement 1 in greater or equal comparison' => [
            <<<'PHP'
                <?php

                if ($foo >= 1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement 1 in smaller comparison' => [
            <<<'PHP'
                <?php

                if ($foo < 1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement 1 in smaller or equal comparison' => [
            <<<'PHP'
                <?php

                if ($foo <= 1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement 1 in equal comparison' => [
            <<<'PHP'
                <?php

                if ($foo == 1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement 1 in not equal comparison' => [
            <<<'PHP'
                <?php

                if ($foo != 1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement 1 in identical comparison' => [
            <<<'PHP'
                <?php

                if ($foo === 1) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It does not decrement 1 in not identical comparison' => [
            <<<'PHP'
                <?php

                if ($foo !== 1) {
                    echo 'bar';
                }
                PHP,
        ];

        foreach (DecrementInteger::NON_NEGATIVE_INT_RETURNING_FUNCTIONS as $name) {
            yield "It does not decrement zero when it is being compared as identical with result of $name" => [sprintf('<?php if (%s() === 0) {}', $name)];
        }

        yield 'It does not decrement when it is accessed zero index of an array' => [
            <<<'PHP'
                <?php
                $b = $a[0];
                PHP,
        ];

        yield 'It does not decrement limit argument of preg_split function when it equals to 0' => [
            <<<'PHP'
                <?php

                preg_split('//', 'string', 0);
                PHP,
        ];

        yield 'It does decrement limit argument of preg_split function when it greater than 0' => [
            <<<'PHP'
                <?php

                preg_split('//', 'string', 1);
                PHP,
            <<<'PHP'
                <?php

                preg_split('//', 'string', 0);
                PHP,
        ];

        yield 'It does decrement limit argument of preg_split function when it equal to -1' => [
            <<<'PHP'
                <?php

                preg_split('//', 'string', -1);
                PHP,
            <<<'PHP'
                <?php

                preg_split('//', 'string', -2);
                PHP,
        ];

        $minInt = PHP_INT_MIN;

        yield 'It does not decrement min int' => [
            <<<"PHP"
                <?php

                if (1 === {$minInt}) {
                    echo 'bar';
                }
                PHP,
        ];

        $maxInt = PHP_INT_MAX;

        yield 'It does not decrement max int negative to avoid parser bugs' => [
            <<<"PHP"
                <?php

                if (1 === -{$maxInt}) {
                    echo 'bar';
                }
                PHP,
        ];

        yield 'It decrements property fetch left' => [
            <<<'PHP'
                <?php

                if ($nodes->someProperty === 0) {}
                PHP,
            <<<'PHP'
                <?php

                if ($nodes->someProperty === -1) {}
                PHP,
        ];

        yield 'It decrements property fetch right' => [
            <<<'PHP'
                <?php

                if (0 === $nodes->someProperty) {}
                PHP,
            <<<'PHP'
                <?php

                if (-1 === $nodes->someProperty) {}
                PHP,
        ];

        yield 'It decrements method call left' => [
            <<<'PHP'
                <?php

                if ($nodes->someMethod() === 0) {}
                PHP,
            <<<'PHP'
                <?php

                if ($nodes->someMethod() === -1) {}
                PHP,
        ];

        yield 'It decrements method call right' => [
            <<<'PHP'
                <?php

                if (0 === $nodes->someMethod()) {}
                PHP,
            <<<'PHP'
                <?php

                if (-1 === $nodes->someMethod()) {}
                PHP,
        ];

        yield 'It does not decrement with *length* property comparison left' => [
            <<<'PHP'
                <?php

                if ($nodes->length === 0) {}
                PHP,
        ];

        yield 'It does not decrement with *length* property comparison right' => [
            <<<'PHP'
                <?php

                if (0 === $nodes->length) {}
                PHP,
        ];

        yield 'It does not decrement with *count* property comparison left' => [
            <<<'PHP'
                <?php

                if ($nodes->countX === 0) {}
                PHP,
        ];

        yield 'It does not decrement with *count* property comparison right' => [
            <<<'PHP'
                <?php

                if (0 === $nodes->countY) {}
                PHP,
        ];

        yield 'It does not decrement with *numberOf* methodCall comparison left' => [
            <<<'PHP'
                <?php

                if ($constructor->getNumberOfParameters() === 0) {}
                PHP,
        ];

        yield 'It does not decrement with *numberOf* methodCall comparison right' => [
            <<<'PHP'
                <?php

                if (0 === $constructor->getNumberOfParameters()) {}
                PHP,
        ];

        yield 'It does not decrement with *numberOf* nullsafe methodCall comparison left' => [
            <<<'PHP'
                <?php

                if ($constructor?->getNumberOfParameters() === 0) {}
                PHP,
        ];

        yield 'It does not decrement with *numberOf* nullsafe methodCall comparison right' => [
            <<<'PHP'
                <?php

                if (0 === $constructor?->getNumberOfParameters()) {}
                PHP,
        ];

        yield 'It does not decrement with *count* nullsafe property comparison left' => [
            <<<'PHP'
                <?php

                if ($nodes?->countX === 0) {}
                PHP,
        ];

        yield 'It does not decrement with *count* nullsafe property comparison right' => [
            <<<'PHP'
                <?php

                if (0 === $nodes?->countY) {}
                PHP,
        ];

        yield 'It does not decrement with *count* variable comparison left' => [
            <<<'PHP'
                <?php

                if ($totalCount !== 0) {}
                PHP,
        ];

        yield 'It does not decrement with *count* variable comparison right' => [
            <<<'PHP'
                <?php

                if (0 !== $totalCounts) {}
                PHP,
        ];

        yield 'It does not decrement with *length* variable comparison left' => [
            <<<'PHP'
                <?php

                if ($xyzLength !== 0) {}
                PHP,
        ];

        yield 'It does not decrement with *length* variable comparison right' => [
            <<<'PHP'
                <?php

                if (0 !== $xyzLength) {}
                PHP,
        ];

        yield 'It does not decrement with *count* property assignment' => [
            <<<'PHP'
                <?php

                $this->callsCount = 0;
                PHP,
        ];

        yield 'It does not decrement with *length* property assignment' => [
            <<<'PHP'
                <?php

                $this->callsLength = 0;
                PHP,
        ];
    }
}
