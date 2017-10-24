<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Process\Listener;

use Infection\Config\InfectionConfig;
use Infection\Console\LogVerbosity;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Events\MutationTestingFinished;
use Infection\Filesystem\Filesystem;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Listener\TextFileLoggerSubscriber;
use PHPUnit\Framework\TestCase;

class TextFileLoggerSubscriberTest extends TestCase
{
    /**
     * @var InfectionConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $infectionConfig;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var MetricsCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metricsCalculator;

    protected function setUp()
    {
        $this->infectionConfig = $this->getMockBuilder(InfectionConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metricsCalculator = $this->getMockBuilder(MetricsCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->getMock();
    }

    public function test_it_do_nothing_when_file_log_path_is_not_defined()
    {
        $this->metricsCalculator->expects($this->never())
            ->method('getEscapedMutantProcesses');

        $this->metricsCalculator->expects($this->never())
            ->method('getTimedOutProcesses');

        $this->metricsCalculator->expects($this->never())
            ->method('getNotCoveredMutantProcesses');

        $this->infectionConfig->expects($this->any())
            ->method('getTextFileLogPath')
            ->willReturn(null);

        $this->filesystem->expects($this->never())
            ->method('dumpFile');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new TextFileLoggerSubscriber(
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->filesystem
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }

    public function test_it_reacts_on_mutation_testing_finished()
    {
        $this->infectionConfig->expects($this->once())
            ->method('getTextFileLogPath')
            ->willReturn(sys_get_temp_dir() . '/infection-log.txt');

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
            ->method('getNotCoveredMutantProcesses')
            ->willReturn([]);

        $this->filesystem->expects($this->once())
            ->method('dumpFile');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new TextFileLoggerSubscriber(
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->filesystem
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }

    public function test_it_reacts_on_mutation_testing_finished_and_debug_mode_off()
    {
        $this->infectionConfig->expects($this->once())
            ->method('getTextFileLogPath')
            ->willReturn(sys_get_temp_dir() . '/infection-log.txt');

        $this->metricsCalculator->expects($this->once())
            ->method('getEscapedMutantProcesses')
            ->willReturn([]);

        $this->metricsCalculator->expects($this->once())
            ->method('getTimedOutProcesses')
            ->willReturn([]);

        $this->metricsCalculator->expects($this->never())
            ->method('getKilledMutantProcesses');

        $this->metricsCalculator->expects($this->once())
            ->method('getNotCoveredMutantProcesses')
            ->willReturn([]);

        $this->filesystem->expects($this->once())
            ->method('dumpFile');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new TextFileLoggerSubscriber(
            $this->infectionConfig,
            $this->metricsCalculator,
            $this->filesystem,
            LogVerbosity::NORMAL
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }
}
