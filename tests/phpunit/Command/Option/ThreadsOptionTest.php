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

use Exception;
use Infection\Command\Option\ThreadsOption;
use Infection\Console\IO;
use Infection\Resource\Processor\CpuCoresCountProvider;
use Infection\Tests\Command\CommandOptionTestCase;
use Infection\Tests\TestingUtility\Console\Command\TestOptionCommand;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(ThreadsOption::class)]
final class ThreadsOptionTest extends CommandOptionTestCase
{
    // TODO: this is necessary because we need to inject CpuCoresCountProvider (for now).
    //  This will be addressed in a follow up PR.
    #[DataProvider('cpuDetectionProvider')]
    public function test_it_only_detects_cpu_cores_for_max(
        string $input,
        int $cpuCoresCount,
        int|Exception|null $expected,
    ): void {
        $io = new IO(
            new StringInput($input),
            new NullOutput(),
        );

        $cpuCoresCountProvider = $this->createMock(CpuCoresCountProvider::class);
        $cpuCoresCountProvider
            ->expects(
                $input === '--threads=max' ? $this->once() : $this->never(),
            )
            ->method('provide')
            ->willReturn($cpuCoresCount)
        ;

        TestOptionCommand::bind(
            ThreadsOption::class,
            $io,
        );

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = ThreadsOption::get($io, $cpuCoresCountProvider);

        $this->assertSame($expected, $actual);
    }

    public static function optionProvider(): iterable
    {
        yield 'not provided' => [
            new StringInput(''),
            null,
        ];

        yield 'numeric string' => [
            new StringInput('--threads=5'),
            5,
        ];

        yield 'short option' => [
            new StringInput('-j5'),
            5,
        ];

        yield 'zero is preserved' => [
            new StringInput('--threads=0'),
            0,
        ];

        yield 'negative value is preserved' => [
            new StringInput('--threads=-1'),
            -1,
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

    public static function cpuDetectionProvider(): iterable
    {
        yield '1 CPU core stays at minimum of 1' => [
            '--threads=max',
            1,
            1,
        ];

        yield '2 CPU cores is 1' => [
            '--threads=max',
            2,
            1,
        ];

        yield '8 CPU cores is 7' => [
            '--threads=max',
            8,
            7,
        ];

        yield 'not provided does not detect CPU cores' => [
            '',
            8,
            null,
        ];

        yield 'numeric value does not detect CPU cores' => [
            '--threads=5',
            8,
            5,
        ];

        yield 'invalid value does not detect CPU cores' => [
            '--threads=abc',
            8,
            new InvalidArgumentException(
                'The value of option `--threads` must be of type integer or string "max". String "abc" provided.',
            ),
        ];
    }

    protected function getOptionClassName(): string
    {
        return ThreadsOption::class;
    }
}
