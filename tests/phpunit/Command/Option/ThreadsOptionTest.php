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

namespace Infection\Tests\Command\Option;

use Infection\Command\Option\ThreadsOption;
use Infection\Tests\Command\CommandOptionTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\StringInput;

#[CoversClass(ThreadsOption::class)]
final class ThreadsOptionTest extends CommandOptionTestCase
{
    public static function optionProvider(): iterable
    {
        yield 'not provided' => [
            new StringInput(''),
            null,
        ];

        yield 'max is preserved' => [
            new StringInput('--threads=max'),
            'max',
        ];

        yield 'numeric string' => [
            new StringInput('--threads=5'),
            5,
        ];

        yield 'short option' => [
            new StringInput('-j5'),
            5,
        ];

        yield 'one is accepted' => [
            new StringInput('--threads=1'),
            1,
        ];

        yield 'zero is preserved' => [
            new StringInput('--threads=0'),
            0,
        ];

        yield 'negative value is preserved' => [
            new StringInput('--threads=-1'),
            -1,
        ];

        yield 'float is truncated to zero' => [
            new StringInput('--threads=0.5'),
            0,
        ];

        yield 'float is truncated to an integer' => [
            new StringInput('--threads=1.5'),
            1,
        ];

        yield 'scientific notation' => [
            new StringInput('--threads=1e2'),
            100,
        ];

        yield 'numeric string with surrounding whitespace' => [
            new StringInput('--threads=" 5 "'),
            5,
        ];

        yield 'invalid value' => [
            new StringInput('--threads=abc'),
            new InvalidArgumentException(
                'The value of option `--threads` must be of type integer or string "max". String "abc" provided.',
            ),
        ];

        yield 'empty value' => [
            new StringInput('--threads='),
            new InvalidArgumentException(
                'The value of option `--threads` must be of type integer or string "max". String "" provided.',
            ),
        ];

        yield 'max is case-sensitive' => [
            new StringInput('--threads=MAX'),
            new InvalidArgumentException(
                'The value of option `--threads` must be of type integer or string "max". String "MAX" provided.',
            ),
        ];

        yield 'max with surrounding whitespace is rejected' => [
            new StringInput('--threads=" max "'),
            new InvalidArgumentException(
                'The value of option `--threads` must be of type integer or string "max". String " max " provided.',
            ),
        ];

        yield 'missing value' => [
            new StringInput('--threads'),
            new RuntimeException('The "--threads" option requires a value.'),
        ];
    }

    protected function getOptionClassName(): string
    {
        return ThreadsOption::class;
    }
}
