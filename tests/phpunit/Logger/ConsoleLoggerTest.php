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

use Infection\Console\IO;
use Infection\Logger\ConsoleLogger;
use function Infection\Tests\normalize_trailing_spaces;
use InvalidArgumentException;
use const PHP_EOL;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Safe\DateTime;
use Safe\DateTimeImmutable;
use function Safe\fopen;
use stdClass;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @group integration
 */
final class ConsoleLoggerTest extends TestCase
{
    public function test_it_throws_a_friendly_error_on_invalid_log_level(): void
    {
        $logger = new ConsoleLogger(IO::createNull());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The log level "unknownLogLevel" does not exist');

        $logger->log('unknownLogLevel', 'foo bar');
    }

    /**
     * @dataProvider outputMappingProvider
     */
    public function test_the_log_level_is_added_to_the_message(
        string $logLevel,
        int $outputVerbosity,
        bool $outputsMessage
    ): void {
        $output = new BufferedOutput($outputVerbosity);

        $logger = new ConsoleLogger(new IO(new StringInput(''), $output));
        $logger->log($logLevel, 'foo bar');

        $logs = $output->fetch();

        $this->assertSame($outputsMessage ? "[$logLevel] foo bar" . PHP_EOL : '', $logs);
    }

    public function test_it_interpolates_the_message_with_the_context(): void
    {
        $output = new BufferedOutput();

        $logger = new ConsoleLogger(new IO(new StringInput(''), $output));

        $logger->log(
            LogLevel::ERROR,
            '{foo} {bar} baz',
            [
                'foo' => 'oof',
                'bar' => 'rab',
                'baz' => 'zab',
            ]
        );

        $this->assertSame(
            '[error] oof rab baz' . PHP_EOL,
            $output->fetch()
        );
    }

    /**
     * @dataProvider valueToCastProvider
     */
    public function test_it_casts_the_context_values_into_strings($value, string $expected): void
    {
        $output = new BufferedOutput();

        $logger = new ConsoleLogger(new IO(new StringInput(''), $output));

        $logger->log(
            LogLevel::ERROR,
            '{value}',
            ['value' => $value]
        );

        $this->assertSame(
            '[error] ' . $expected . PHP_EOL,
            $output->fetch()
        );
    }

    public function test_it_uses_the_io_blocks_when_passing_the_block_context(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL);

        $logger = new ConsoleLogger(new IO(new StringInput(''), $output));

        $logger->log(LogLevel::NOTICE, 'message', ['block' => true]);

        $this->assertSame(
            <<<'TXT'

 ! [NOTE] message


TXT
            ,
            normalize_trailing_spaces($output->fetch())
        );

        $logger->log(LogLevel::WARNING, 'message', ['block' => true]);

        $this->assertSame(
            <<<'TXT'
 [WARNING] message


TXT
            ,
            normalize_trailing_spaces($output->fetch())
        );

        $logger->log(LogLevel::ERROR, 'message', ['block' => true]);

        $this->assertSame(
            <<<'TXT'
 [ERROR] message


TXT
            ,
            normalize_trailing_spaces($output->fetch())
        );
    }

    public static function valueToCastProvider(): iterable
    {
        yield 'string' => ['oof', 'oof'];

        yield 'bool' => [true, '1'];

        yield 'null' => ['null', 'null'];

        yield 'int' => [10, '10'];

        yield 'float' => [10.8, '10.8'];

        yield 'object' => [new stdClass(), '[object stdClass]'];

        yield 'datetime' => [
            DateTimeImmutable::createFromFormat(
                DateTime::ATOM,
                '2020-04-26T07:32:25+00:00'
            ),
            '2020-04-26T07:32:25+00:00',
        ];

        yield 'nested' => [
            ['with object' => new stdClass()],
            '[array]',
        ];

        yield 'resource' => [
            fopen('php://memory', 'rb'),
            '[resource]',
        ];

        yield 'callable' => [
            static function (): void {},
            '[object Closure]',
        ];
    }

    public static function outputMappingProvider(): iterable
    {
        yield [LogLevel::EMERGENCY, OutputInterface::VERBOSITY_NORMAL, true];

        yield [LogLevel::WARNING, OutputInterface::VERBOSITY_NORMAL, true];

        yield [LogLevel::INFO, OutputInterface::VERBOSITY_NORMAL, false];

        yield [LogLevel::DEBUG, OutputInterface::VERBOSITY_NORMAL, false];

        yield [LogLevel::INFO, OutputInterface::VERBOSITY_VERBOSE, true];

        yield [LogLevel::DEBUG, OutputInterface::VERBOSITY_VERY_VERBOSE, false];

        yield [LogLevel::DEBUG, OutputInterface::VERBOSITY_DEBUG, true];

        yield [LogLevel::ALERT, OutputInterface::VERBOSITY_QUIET, false];

        yield [LogLevel::EMERGENCY, OutputInterface::VERBOSITY_QUIET, false];
    }
}
