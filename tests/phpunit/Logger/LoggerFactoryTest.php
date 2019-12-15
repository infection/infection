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

namespace Infection\Tests\Logger;

use Generator;
use Infection\Console\LogVerbosity;
use Infection\Logger\BadgeLogger;
use Infection\Logger\DebugFileLogger;
use Infection\Logger\LoggerFactory;
use Infection\Logger\NullLogger;
use Infection\Logger\PerMutatorLogger;
use Infection\Logger\ResultsLoggerTypes;
use Infection\Logger\SummaryFileLogger;
use Infection\Logger\TextFileLogger;
use Infection\Logger\UnknownLogType;
use Infection\Mutant\MetricsCalculator;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class LoggerFactoryTest extends TestCase
{
    /**
     * @dataProvider provideLogTypesThatAreNotAllowedOnLowVerbosity
     */
    public function test_it_creates_null_logger_for_low_verbosity(string $logType): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NONE,
            true,
            true
        );

        $this->assertInstanceOf(NullLogger::class, $factory->createLogger(
            $this->createMock(OutputInterface::class),
            $logType,
            ''
        ));
    }

    public function provideLogTypesThatAreNotAllowedOnLowVerbosity(): Generator
    {
        yield [ResultsLoggerTypes::DEBUG_FILE];

        yield [ResultsLoggerTypes::PER_MUTATOR];

        yield [ResultsLoggerTypes::SUMMARY_FILE];

        yield [ResultsLoggerTypes::TEXT_FILE];
    }

    public function test_it_throws_an_exception_on_unknown_log_type(): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NORMAL,
            true,
            true
        );

        $this->expectException(UnknownLogType::class);
        $factory->createLogger(
            $this->createMock(OutputInterface::class),
            'foo',
            ''
        );
    }

    /**
     * @dataProvider provideLogTypesAndClasses
     */
    public function test_it_creates_logger_for_log_type(string $logType, string $expectedLogClass): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NORMAL,
            true,
            true
        );

        $this->assertInstanceOf($expectedLogClass, $factory->createLogger(
            $this->createMock(OutputInterface::class),
            $logType,
            ''
        ));
    }

    public function provideLogTypesAndClasses(): Generator
    {
        yield [ResultsLoggerTypes::TEXT_FILE, TextFileLogger::class];

        yield [ResultsLoggerTypes::PER_MUTATOR, PerMutatorLogger::class];

        yield [ResultsLoggerTypes::SUMMARY_FILE, SummaryFileLogger::class];

        yield [ResultsLoggerTypes::DEBUG_FILE, DebugFileLogger::class];
    }

    public function test_it_creates_a_bade_logger(): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NORMAL,
            true,
            true
        );

        $this->assertInstanceOf(BadgeLogger::class, $factory->createLogger(
            $this->createMock(OutputInterface::class),
            ResultsLoggerTypes::BADGE,
            new stdClass()
        ));
    }

    public function test_it_creates_a_bade_logger_on_low_verbosity(): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NONE,
            true,
            true
        );

        $this->assertInstanceOf(BadgeLogger::class, $factory->createLogger(
            $this->createMock(OutputInterface::class),
            ResultsLoggerTypes::BADGE,
            new stdClass()
        ));
    }
}
