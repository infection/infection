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
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class SummaryFileLoggerTest extends TestCase
{
    /**
     * @var TmpDirectoryCreator
     */
    private $creator;

    /**
     * @var string
     */
    private $workspace;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $tmpDir;

    protected function setUp(): void
    {
        $this->fileSystem = new Filesystem();
        $this->creator = new TmpDirectoryCreator($this->fileSystem);
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);
        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_logs_the_correct_lines_with_no_mutations(): void
    {
        $logFilePath = $this->tmpDir . '/foo.txt';
        $calculator = new MetricsCalculator();
        $output = $this->createMock(OutputInterface::class);
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

        $debugFileLogger = new SummaryFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    public function test_it_logs_the_correct_lines_with_mutations(): void
    {
        $logFilePath = $this->tmpDir . '/foo.txt';
        $calculator = $this->createMock(MetricsCalculator::class);
        $calculator->expects($this->once())->method('getTotalMutantsCount')->willReturn(6);
        $calculator->expects($this->once())->method('getKilledCount')->willReturn(8);
        $calculator->expects($this->once())->method('getErrorCount')->willReturn(7);
        $calculator->expects($this->once())->method('getEscapedCount')->willReturn(30216);
        $calculator->expects($this->once())->method('getTimedOutCount')->willReturn(2);
        $calculator->expects($this->once())->method('getNotCoveredByTestsCount')->willReturn(0);
        $output = $this->createMock(OutputInterface::class);
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

        $debugFileLogger = new SummaryFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    public function test_it_outputs_an_error_when_dir_is_not_writable(): void
    {
        $readOnlyDirPath = $this->tmpDir . '/invalid';
        $logFilePath = $readOnlyDirPath . '/foo.txt';

        // make it readonly
        $this->fileSystem->mkdir($readOnlyDirPath, 0400);

        $calculator = $this->createMock(MetricsCalculator::class);
        $calculator->expects($this->once())->method('getTotalMutantsCount')->willReturn(6);
        $calculator->expects($this->once())->method('getKilledCount')->willReturn(8);
        $calculator->expects($this->once())->method('getErrorCount')->willReturn(7);
        $calculator->expects($this->once())->method('getEscapedCount')->willReturn(30216);
        $calculator->expects($this->once())->method('getTimedOutCount')->willReturn(2);
        $calculator->expects($this->once())->method('getNotCoveredByTestsCount')->willReturn(0);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with(
            sprintf(
                '<error>Unable to write to the "%s" directory.</error>',
                $readOnlyDirPath
            )
        );

        $fs = new Filesystem();

        $debugFileLogger = new SummaryFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }
}
