<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Listener;

use Infection\Config\InfectionConfig;
use Infection\Configuration\Configuration;
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

    protected function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->infectionConfig = $this->createMock(Configuration::class);
        $this->metricsCalculator = $this->createMock(MetricsCalculator::class);
        $this->filesystem = $this->createMock(Filesystem::class);
    }

    public function test_it_do_nothing_when_file_log_path_is_not_defined(): void
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

    public function test_it_reacts_on_mutation_testing_finished(): void
    {
        $logTypes = ['text' => sys_get_temp_dir() . '/infection.log'];

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

    public function test_it_reacts_on_mutation_testing_finished_and_debug_mode_off(): void
    {
        $logTypes = ['text' => sys_get_temp_dir() . '/infection.log'];

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

    public function test_it_reacts_on_mutation_testing_finished_and_no_file_logging(): void
    {
        $logTypes = ['text' => sys_get_temp_dir() . '/infection.log'];

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

    public function test_it_reacts_to_other_logging_types(): void
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
