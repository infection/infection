<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Process\Listener\FileLogger;

use Infection\Filesystem\Filesystem;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Listener\FileLogger\DebugFileLogger;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DebugFileLoggerTest extends MockeryTestCase
{
    private $filename = 'test-file.txt';

    public function test_it_writes_correct_summary()
    {
        $calculator = $this->mockMetrics();

        $fs = Mockery::mock(Filesystem::class);
        $fs->shouldReceive('dumpFile')
            ->withArgs(
                [
                    $this->filename,
                    <<<TXT
Total: 8
Killed mutants:
===============


Mutator: PublicVisibility
Line 3

Errors mutants:
===============


Mutator: Plus
Line 7

Escaped mutants:
================


Timed Out mutants:
==================


Not Covered mutants:
====================


TXT
                    ,
                ]
            );
        $logger = new DebugFileLogger(
            $this->filename,
            $calculator,
            $fs,
            true
        );
        $logger->writeToFile();
    }

    /**
     * @return MetricsCalculator| Mockery\MockInterface
     */
    private function mockMetrics()
    {
        $calculator = Mockery::mock(MetricsCalculator::class);

        $calculator->shouldReceive('getTotalMutantsCount')
            ->andReturn(8);

        $killMock = Mockery::mock(\stdClass::class);
        $killMock->shouldReceive('getMutant->getMutation')
            ->andReturn($killMock);

        $killMock->shouldReceive('getMutator->getName')
            ->andReturn('PublicVisibility');

        $killMock->shouldReceive('getAttributes')
            ->andReturn(['startLine' => 3]);

        $errorMock = Mockery::mock(\stdClass::class);

        $errorMock->shouldReceive('getMutant->getMutation')
            ->andReturn($errorMock);

        $errorMock->shouldReceive('getMutator->getName')
            ->andReturn('Plus');

        $errorMock->shouldReceive('getAttributes')
            ->andReturn(['startLine' => 7]);

        $calculator->shouldReceive('getKilledMutantProcesses')
            ->andReturn([$killMock]);

        $calculator->shouldReceive('getErrorProcesses')
            ->andReturn([$errorMock]);

        $calculator->shouldReceive('getEscapedMutantProcesses')
            ->andReturn([]);

        $calculator->shouldReceive('getTimedOutProcesses')
            ->andReturn([]);

        $calculator->shouldReceive('getNotCoveredMutantProcesses')
            ->andReturn([]);

        return $calculator;
    }
}
