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

use Infection\Mutator\Operator\Throw_;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Throw_::class)]
final class Throw_Test extends BaseMutatorTestCase
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
        yield 'It removes the throw expression' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    throw new \Exception();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    new \Exception();
                    PHP,
            ),
        ];

        yield 'It mutates throw in match non-default arm' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    match ($x) {
                        0 => throw new \Exception(),
                        default => '',
                    };
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    match ($x) {
                        0 => new \Exception(),
                        default => '',
                    };
                    PHP,
            ),
        ];

        yield 'It does not mutate throw in match default arm to prevent overlap with MatchArmRemoval' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    match ($x) {
                        default => throw new \Exception(),
                    };
                    PHP,
            ),
        ];

        yield 'It mutates throw in switch non-default case' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch ($x) {
                        case true:
                            throw new \Exception();
                        default:
                            $s = '';
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch ($x) {
                        case true:
                            new \Exception();
                        default:
                            $s = '';
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate throw in switch default-arm to prevent overlap with SharedCaseRemoval' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch ($x) {
                        case true: $s = ''; break;
                        default: throw new \Exception();
                    }
                    PHP,
            ),
        ];
    }
}
