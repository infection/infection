<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Listener;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Events\InitialTestSuiteStarted;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Mockery;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class InitialTestsConsoleLoggerSubscriberTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function test_it_reacts_on_initial_test_suite_run()
    {
        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('isDecorated');
        $output->shouldReceive('writeln');
        $output->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_QUIET);

        $progressBar = new ProgressBar($output);

        $testFramework = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $testFramework->shouldReceive('getName')->once();
        $testFramework->shouldReceive('getVersion')->once();

        $subscriber = new InitialTestsConsoleLoggerSubscriber(
            $output,
            $progressBar,
            $testFramework
        );

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($subscriber);

        $dispatcher->dispatch(new InitialTestSuiteStarted());
    }

    public function test_it_sets_test_framework_version_as_unknown_in_case_of_exception()
    {
        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('isDecorated');
        $output->shouldReceive('writeln')->once()->withArgs([[
            'Running initial test suite...',
            '',
            'PHPUnit version: unknown',
            '',
        ]]);
        $output->shouldReceive('getVerbosity')->andReturn(OutputInterface::VERBOSITY_QUIET);

        $progressBar = new ProgressBar($output);

        $testFramework = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $testFramework->shouldReceive('getName')->once()->andReturn('PHPUnit');
        $testFramework->shouldReceive('getVersion')->andThrow(\InvalidArgumentException::class);

        $subscriber = new InitialTestsConsoleLoggerSubscriber(
            $output,
            $progressBar,
            $testFramework
        );

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($subscriber);

        $dispatcher->dispatch(new InitialTestSuiteStarted());
    }
}
