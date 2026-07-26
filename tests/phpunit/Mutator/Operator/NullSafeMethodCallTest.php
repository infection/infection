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

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Operator\NullSafeMethodCall;
use Infection\Testing\BaseMutatorTestCase;
use const PHP_VERSION_ID;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(NullSafeMethodCall::class)]
final class NullSafeMethodCallTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array $expected = []): void
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('Null Safe operator is available only in PHP 8 or higher');
        }

        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'Mutate nullsafe method call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class?->getName();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class->getName();
                    PHP,
            ),
        ];

        yield 'Mutate nullsafe method call only' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class?->getName()?->property;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class->getName()?->property;
                    PHP,
            ),
        ];

        yield 'Mutate chain of nullsafe method calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class?->getObject()?->getName();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $class->getObject()?->getName();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $class?->getObject()->getName();
                        PHP,
                ),
            ],
        ];

        yield 'Mutate nullsafe applied right when class has been instantiated' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    (new SomeClass())?->methodCall();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    (new SomeClass())->methodCall();
                    PHP,
            ),
        ];

        yield 'Mutate nullsafe with dynamic method name' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class?->{$methodCall}();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class->{$methodCall}();
                    PHP,
            ),
        ];
    }
}
