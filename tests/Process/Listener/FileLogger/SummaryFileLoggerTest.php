<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Process\Listener\FileLogger;

use Infection\Mutant\MetricsCalculator;
use Mockery;
use Infection\Filesystem\Filesystem;
use Infection\Process\Listener\FileLogger\SummaryFileLogger;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SummaryFileLoggerTest extends MockeryTestCase
{
    private $filename = 'test-file.txt';

    public function test_it_writes_correct_summary()
    {
        $calculator = Mockery::mock(MetricsCalculator::class);

        $calculator->shouldReceive('getTotalMutantsCount')
            ->andReturn(30);
        $calculator->shouldReceive('getKilledCount')
            ->andReturn(10);
        $calculator->shouldReceive('getErrorCount')
            ->andReturn(3);
        $calculator->shouldReceive('getEscapedCount')
            ->andReturn(2);
        $calculator->shouldReceive('getTimedOutCount')
            ->andReturn(7);

        $calculator->shouldReceive('getNotCoveredByTestsCount')
            ->andReturn(8);

        $fs = Mockery::mock(Filesystem::class);

        $fs->shouldReceive('dumpFile')
            ->withArgs([
                $this->filename,
                <<<TXT
Total: 30
Killed: 10
Errored: 3
Escaped: 2
Timed Out: 7
Not Covered: 8
TXT
                ,
            ])
        ->once();

        $logger = new SummaryFileLogger(
            $this->filename,
            $calculator,
            $fs,
            true
        );
        $logger->writeToFile();
    }
}
