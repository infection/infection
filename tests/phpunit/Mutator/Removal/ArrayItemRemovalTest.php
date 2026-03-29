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

namespace Infection\Tests\Mutator\Removal;

use Infection\Mutator\Removal\ArrayItemRemoval;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Tests\Mutator\MutatorFixturesProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[CoversClass(ArrayItemRemoval::class)]
final class ArrayItemRemovalTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     * @param array<string, string|int> $settings
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array $expected = [], array $settings = []): void
    {
        $this->assertMutatesInput($input, $expected, $settings);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It does not mutate empty arrays' => [
            self::wrapCodeInMethod('$a = [];'),
        ];

        yield 'It removes only first item by default' => [
            self::wrapCodeInMethod('$a = [1, 2, 3];'),
            self::wrapCodeInMethod('$a = [2, 3];'),
        ];

        yield 'It removes only last item when set to do so' => [
            self::wrapCodeInMethod('$a = [1, 2, 3];'),
            self::wrapCodeInMethod('$a = [1, 2];'),
            ['remove' => 'last'],
        ];

        yield 'It removes every item on by one when set to `all`' => [
            self::wrapCodeInMethod('$a = [1, 2, 3];'),
            [
                self::wrapCodeInMethod('$a = [2, 3];'),
                self::wrapCodeInMethod('$a = [1, 3];'),
                self::wrapCodeInMethod('$a = [1, 2];'),
            ],
            ['remove' => 'all'],
        ];

        yield 'It obeys limit when mutating arrays in `all` mode' => [
            self::wrapCodeInMethod('$a = [1, 2, 3];'),
            [
                self::wrapCodeInMethod('$a = [2, 3];'),
                self::wrapCodeInMethod('$a = [1, 3];'),
            ],
            ['remove' => 'all', 'limit' => 2],
        ];

        yield 'It mutates arrays having required items count when removing `all` items' => [
            self::wrapCodeInMethod('$a = [1, 2];'),
            [
                self::wrapCodeInMethod('$a = [2];'),
                self::wrapCodeInMethod('$a = [1];'),
            ],
            ['remove' => 'all', 'limit' => 2],
        ];

        yield 'It mutates correctly for limit value (1)' => [
            self::wrapCodeInMethod('$a = [1];'),
            [
                self::wrapCodeInMethod('$a = [];'),
            ],
            ['remove' => 'all', 'limit' => 1],
        ];

        yield 'It does not mutate lists with missing elements' => [
            self::wrapCodeInMethod('[, $a] = [];'),
        ];

        yield 'It does not mutate lists with one element' => [
            self::wrapCodeInMethod('[$a] = [];'),
        ];

        yield 'It does not mutate array assignment to prevent runtime warning' => [
            self::wrapCodeInMethod('[$a, $b] = [$c, $d];'),
        ];

        yield 'It mutates array assignment with more elements on the right side' => [
            self::wrapCodeInMethod('[$a, $b] = [$c, $d, $e];'),
            self::wrapCodeInMethod('[$a, $b] = [$d, $e];'),
        ];

        yield 'It does not mutate lists with any number of elements' => [
            self::wrapCodeInMethod('[$a, $b] = [];'),
        ];

        yield 'It does not mutate arrays as an attribute argument' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'does-not-mutate-array-in-attribute.php'),
        ];

        yield 'It does not mutate destructured array values in foreach loops' => [
            self::wrapCodeInMethod('foreach ($items as [, $value]) {}'),
        ];

        yield 'It does not mutate in_array to prevent overlap with IfNegation' => [
            self::wrapCodeInMethod('if (in_array($a, [$b])) {}'),
        ];

        yield 'It does not mutate array_key_exists to prevent overlap with IfNegation' => [
            self::wrapCodeInMethod('if (array_key_exists($a, [$b])) {}'),
        ];

        yield 'It mutates array_search which does not return bool, therefore not overlaps with IfNegation' => [
            self::wrapCodeInMethod('if (array_search($a, [$b])) {}'),
            self::wrapCodeInMethod('if (array_search($a, [])) {}'),
        ];

        yield 'It mutates arg of a userland function' => [
            self::wrapCodeInMethod('if (doFoo($a, [$b])) {}'),
            self::wrapCodeInMethod('if (doFoo($a, [])) {}'),
        ];

        yield 'It mutates arg of a dynamic function call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $fn = "doFoo";
                    if ($fn($a, [$b])) {}
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $fn = "doFoo";
                    if ($fn($a, [])) {}
                    PHP,
            ),
        ];
    }
}
