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

use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(MethodCallRemoval::class)]
final class MethodCallRemovalTest extends BaseMutatorTestCase
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
        yield 'It removes a method call without parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->foo();
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It removes a method call with parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $foo->bar(3, 4);
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It remove a static method call without parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    self::foo();
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It remove a static method call with parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    THatClass::bar(3, 4);
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It remove a null-safe method call with parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $foo?->bar(3, 4);
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It does not remove a method call that is assigned to something' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $b = $this->foo();
                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It does not remove a method call within a statement' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($this->foo()) {
                        $a = 3;
                    }
                    while ($foo->foo()) {
                        $a = 3;
                    }

                    PHP,
            ),
        ];

        yield 'It does not remove a method call that is the parameter of another function or method' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = $this->foo(3, $a->bar());
                    PHP,
            ),
        ];

        yield 'It does not remove a function call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foo();
                    $a = 3;
                    PHP,
            ),
        ];
    }
}
