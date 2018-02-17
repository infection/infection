<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Process\Runner;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\InitialTestCaseCompleted;
use Infection\Events\InitialTestSuiteFinished;
use Infection\Events\InitialTestSuiteStarted;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Runner\InitialTestsRunner;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Process\Process;

class InitialTestsRunnerTest extends MockeryTestCase
{
    public function test_it_dispatches_events()
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('run');

        $processBuilder = Mockery::mock(ProcessBuilder::class);
        $processBuilder
            ->shouldReceive('getProcessForInitialTestRun')
            ->withArgs([''])
            ->andReturn($process);

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->with(Mockery::type(InitialTestSuiteStarted::class));
        $eventDispatcher->shouldReceive('dispatch')->with(Mockery::type(InitialTestCaseCompleted::class));
        $eventDispatcher->shouldReceive('dispatch')->with(Mockery::type(InitialTestSuiteFinished::class));

        $testRunner = new InitialTestsRunner($processBuilder, $eventDispatcher);

        $testRunner->run('');
    }
}
