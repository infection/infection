<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Listener;

use Infection\Config\InfectionConfig;
use Infection\Console\LogVerbosity;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Events\MutationTestingFinished;
use Infection\Logger\ResultsLoggerTypes;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Listener\MutationTestingResultsLoggerSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class MutationTestingResultsLoggerSubscriberTest extends TestCase
{
    /**
     * @var OutputInterface|MockObject
     */
    private $output;

    /**
     * @var InfectionConfig|MockObject
     */
    private $infectionConfig;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var MetricsCalculator|MockObject
     */
    private $metricsCalculator;

    protected function setUp()
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->infectionConfig = $this->createMock(InfectionConfig::class);
        $this->metricsCalculator = $this->createMock(MetricsCalculator::class);
        $this->filesystem = $this->createMock(Filesystem::class);
    }

    public function test_it_do_nothing_when_file_log_path_is_not_defined()
    {
        $this->metricsCalculator->expects($this->never())
            ->method('getEscapedMutantProcesses');

        $this->metricsCalculator->expects($this->never())
            ->method('getTimedOutProcesses');

        $this->metricsCalculator->expects($this->never())
            ->method('getNotCoveredMutantProcesses');

        $this->filesystem->expects($this->never())
            ->method('dumpFile');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingResultsLoggerSubscriber(
            $this->output,
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->filesystem,
            LogVerbosity::DEBUG,
            true
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }

    public function test_it_reacts_on_mutation_testing_finished()
    {
        $logTypes = ['text' => sys_get_temp_dir() . '/infection-log.txt'];

        $this->infectionConfig->expects($this->once())
            ->method('getLogsTypes')
            ->willReturn($logTypes);

        $this->metricsCalculator->expects($this->once())
            ->method('getEscapedMutantProcesses')
            ->willReturn([]);

        $this->metricsCalculator->expects($this->once())
            ->method('getTimedOutProcesses')
            ->willReturn([]);

        $this->metricsCalculator->expects($this->once())
            ->method('getKilledMutantProcesses')
            ->willReturn([]);

        $this->metricsCalculator->expects($this->once())
            ->method('getErrorProcesses')
            ->willReturn([]);

        $this->metricsCalculator->expects($this->once())
            ->method('getNotCoveredMutantProcesses')
            ->willReturn([]);

        $this->filesystem->expects($this->once())
            ->method('dumpFile');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingResultsLoggerSubscriber(
            $this->output,
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->filesystem,
            LogVerbosity::DEBUG,
            true
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }

    public function test_it_reacts_on_mutation_testing_finished_and_debug_mode_off()
    {
        $logTypes = ['text' => sys_get_temp_dir() . '/infection-log.txt'];

        $this->infectionConfig->expects($this->once())
            ->method('getLogsTypes')
            ->willReturn($logTypes);

        $this->metricsCalculator->expects($this->once())
            ->method('getEscapedMutantProcesses')
            ->willReturn([]);

        $this->metricsCalculator->expects($this->once())
            ->method('getTimedOutProcesses')
            ->willReturn([]);

        $this->metricsCalculator->expects($this->never())
            ->method('getKilledMutantProcesses');

        $this->metricsCalculator->expects($this->never())
            ->method('getErrorProcesses');

        $this->metricsCalculator->expects($this->once())
            ->method('getNotCoveredMutantProcesses')
            ->willReturn([]);

        $this->filesystem->expects($this->once())
            ->method('dumpFile');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingResultsLoggerSubscriber(
            $this->output,
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->filesystem,
            LogVerbosity::NORMAL,
            false
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }

    public function test_it_reacts_on_mutation_testing_finished_and_no_file_logging()
    {
        $logTypes = ['text' => sys_get_temp_dir() . '/infection-log.txt'];

        $this->infectionConfig->expects($this->once())
            ->method('getLogsTypes')
            ->willReturn($logTypes);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingResultsLoggerSubscriber(
            $this->output,
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->filesystem,
            LogVerbosity::NONE,
            true
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }

    public function test_it_reacts_to_other_logging_types()
    {
        $logTypes = [ResultsLoggerTypes::PER_MUTATOR => sys_get_temp_dir() . '/infection-log.md'];

        $this->infectionConfig->expects($this->once())
            ->method('getLogsTypes')
            ->willReturn($logTypes);

        $this->output->expects($this->never())
            ->method($this->anything());

        $this->metricsCalculator->expects($this->once())
            ->method('getAllMutantProcesses')
            ->willReturn([]);

        $this->filesystem->expects($this->once())
            ->method('dumpFile');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingResultsLoggerSubscriber(
            $this->output,
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->filesystem,
            LogVerbosity::DEBUG,
            true
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }
}
