<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Logger;

use Infection\Logger\SummaryFileLogger;
use Infection\Mutant\MetricsCalculator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class SummaryFileLoggerTest extends TestCase
{
    public function test_it_logs_the_correct_lines_with_no_mutations(): void
    {
        $logFilePath = sys_get_temp_dir() . '/foo.txt';
        $calculator = new MetricsCalculator();
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('dumpFile')->with(
            $logFilePath,
            <<<'TXT'
Total: 0
Killed: 0
Errored: 0
Escaped: 0
Timed Out: 0
Not Covered: 0
TXT
        );

        $debugFileLogger = new SummaryFileLogger($logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    public function test_it_logs_the_correct_lines_with_mutations(): void
    {
        $logFilePath = sys_get_temp_dir() . '/foo.txt';
        $calculator = $this->createMock(MetricsCalculator::class);
        $calculator->expects($this->once())->method('getTotalMutantsCount')->willReturn(6);
        $calculator->expects($this->once())->method('getKilledCount')->willReturn(8);
        $calculator->expects($this->once())->method('getErrorCount')->willReturn(7);
        $calculator->expects($this->once())->method('getEscapedCount')->willReturn(30216);
        $calculator->expects($this->once())->method('getTimedOutCount')->willReturn(2);
        $calculator->expects($this->once())->method('getNotCoveredByTestsCount')->willReturn(0);
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('dumpFile')->with(
            $logFilePath,
            <<<'TXT'
Total: 6
Killed: 8
Errored: 7
Escaped: 30216
Timed Out: 2
Not Covered: 0
TXT
        );

        $debugFileLogger = new SummaryFileLogger($logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }
}
