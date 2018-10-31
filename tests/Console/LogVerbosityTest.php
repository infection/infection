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

namespace Infection\Tests\Console;

use Infection\Console\ConsoleOutput;
use Infection\Console\LogVerbosity;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
final class LogVerbosityTest extends MockeryTestCase
{
    public function test_it_works_if_verbosity_is_valid(): void
    {
        $input = $this->setInputExpectationsWhenItDoesNotChange(LogVerbosity::NORMAL);

        LogVerbosity::convertVerbosityLevel($input, new ConsoleOutput(Mockery::mock(SymfonyStyle::class)));
    }

    /**
     * @dataProvider provideConvertedLogVerbosity
     *
     * @param int $input
     * @param string $output
     */
    public function test_it_converts_int_version_to_string_version_of_verbosity(int $input, string $output): void
    {
        $input = $this->setInputExpectationsWhenItDoesChange($input, $output);
        $io = Mockery::mock(SymfonyStyle::class);
        $io->shouldReceive('note')
            ->withArgs(['Numeric versions of log-verbosity have been deprecated, please use, ' . $output . ' to keep the same result'])
            ->once();

        LogVerbosity::convertVerbosityLevel($input, new ConsoleOutput($io));
    }

    public function provideConvertedLogVerbosity()
    {
        yield 'It converts none integer to none' => [
            LogVerbosity::NONE_INTEGER,
            LogVerbosity::NONE,
        ];

        yield 'It converts normal integer to normal' => [
            LogVerbosity::NORMAL_INTEGER,
            LogVerbosity::NORMAL,
        ];

        yield 'It converts debug integer to debug' => [
            LogVerbosity::DEBUG_INTEGER,
            LogVerbosity::DEBUG,
        ];

        yield 'It converts string version of debug integer to debug' => [
            (string) LogVerbosity::DEBUG_INTEGER,
            LogVerbosity::DEBUG,
        ];
    }

    public function test_it_converts_to_normal_and_writes_notice_when_invalid_verbosity(): void
    {
        $input = $this->setInputExpectationsWhenItDoesChange('asdf', LogVerbosity::NORMAL);
        $io = Mockery::mock(SymfonyStyle::class);
        $io->shouldReceive('note')
            ->withArgs(['Running infection with an unknown log-verbosity option, falling back to default option'])
            ->once();

        LogVerbosity::convertVerbosityLevel($input, new ConsoleOutput($io));
    }

    /**
     * @param string|int $inputVerbosity
     *
     * @return InputInterface|Mockery\MockInterface
     */
    private function setInputExpectationsWhenItDoesNotChange($inputVerbosity)
    {
        $input = Mockery::mock(InputInterface::class);
        $input->shouldReceive('getOption')
            ->withArgs(['log-verbosity'])
            ->once()
            ->andReturn($inputVerbosity);

        return $input;
    }

    /**
     * @param string|int $input
     * @param string $output
     *
     * @return InputInterface|Mockery\MockInterface
     */
    private function setInputExpectationsWhenItDoesChange($input, string $output)
    {
        $input = $this->setInputExpectationsWhenItDoesNotChange($input);
        $input->shouldReceive('setOption')
            ->withArgs(['log-verbosity', $output])
            ->once();

        return $input;
    }
}
