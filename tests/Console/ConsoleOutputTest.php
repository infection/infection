<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Console;

use Infection\Console\ConsoleOutput;
use Infection\Mutant\Exception\MsiCalculationException;
use Infection\Mutant\MetricsCalculator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class ConsoleOutputTest extends TestCase
{
    public function test_log_verbosity_deprecation_notice()
    {
        $option = 'all';
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('note')
            ->with(
                'Numeric versions of log-verbosity have been deprecated, please use, ' . $option . ' to keep the same result'
            );

        $consoleOutput = new ConsoleOutput($io);
        $consoleOutput->logVerbosityDeprecationNotice($option);
    }

    public function test_log_unknown_verbosity_option()
    {
        $option = 'default';
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('note')
            ->with(
                'Running infection with an unknown log-verbosity option, falling back to ' . $option . ' option'
            );

        $consoleOutput = new ConsoleOutput($io);
        $consoleOutput->logUnkownVerbosityOption($option);
    }

    public function test_log_initial_tests_do_not_pass()
    {
        $testFrameworkKey = 'phpunit';
        $process = $this->createMock(Process::class);
        $process->expects($this->once())->method('getExitCode')->willReturn(0);
        $process->expects($this->once())->method('getOutput')->willReturn('output string');
        $process->expects($this->once())->method('getErrorOutput')->willReturn('error string');

        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('error')->with(
            [
                'Project tests must be in a passing state before running Infection.',
                'phpunit reported an exit code of 0.',
                'Refer to the phpunit\'s output below:',
                'STDOUT:',
                'output string',
                'STDERR:',
                'error string',
            ]
        );

        $console = new ConsoleOutput($io);
        $console->logInitialTestsDoNotPass($process, $testFrameworkKey);
    }

    public function test_log_bad_msi_error_message()
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getMutationScoreIndicator')->willReturn('75.0');
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('error')->with(
            'The minimum required MSI percentage should be 25%, but actual is 75%. Improve your tests!'
        );

        $console = new ConsoleOutput($io);

        $console->logBadMsiErrorMessage($metrics, 25.0);
    }

    public function test_log_bad_msi_error_message_throws_error_on_faulty_msi()
    {
        $io = $this->createMock(SymfonyStyle::class);
        $consoleOutput = new ConsoleOutput($io);

        $this->expectException(MsiCalculationException::class);

        $consoleOutput->logBadMsiErrorMessage(new MetricsCalculator(), 0.0);
    }

    public function test_log_bad_covered_msi_error_message_throws_error_on_faulty_msi()
    {
        $io = $this->createMock(SymfonyStyle::class);
        $consoleOutput = new ConsoleOutput($io);

        $this->expectException(MsiCalculationException::class);

        $consoleOutput->logBadCoveredMsiErrorMessage(new MetricsCalculator(), 0.0);
    }

    public function test_log_bad_covered_msi_error_message()
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getCoveredCodeMutationScoreIndicator')->willReturn('75.0');
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('error')->with(
            'The minimum required Covered Code MSI percentage should be 25%, but actual is 75%. Improve your tests!'
        );

        $console = new ConsoleOutput($io);

        $console->logBadCoveredMsiErrorMessage($metrics, 25.0);
    }
}
