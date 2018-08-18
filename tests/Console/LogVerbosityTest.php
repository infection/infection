<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
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
