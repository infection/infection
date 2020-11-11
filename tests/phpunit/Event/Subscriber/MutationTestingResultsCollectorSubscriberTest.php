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

namespace Infection\Tests\Event\Subscriber;

use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\Subscriber\MutationTestingResultsCollectorSubscriber;
use Infection\Metrics\Collector;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\MutantExecutionResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class MutationTestingResultsCollectorSubscriberTest extends TestCase
{
    /**
     * @var OutputInterface|MockObject
     */
    private $output;

    /**
     * @var OutputFormatter|MockObject
     */
    private $outputFormatter;

    /**
     * @var MetricsCalculator|MockObject
     */
    private $metricsCalculator;

    /**
     * @var ResultsCollector|MockObject
     */
    private $resultsCollector;

    /**
     * @var DiffColorizer|MockObject
     */
    private $diffColorizer;

    public function test_it_reacts_on_mutation_process_finished(): void
    {
        $collectorA = $this->createMock(Collector::class);
        $collectorA
            ->expects($this->once())
            ->method('collect')
        ;

        $collectorB = $this->createMock(Collector::class);
        $collectorB
            ->expects($this->once())
            ->method('collect')
        ;

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingResultsCollectorSubscriber(
            $collectorA,
            $collectorB
        ));

        $dispatcher->dispatch(
            new MutantProcessWasFinished(
                $this->createMock(MutantExecutionResult::class)
            )
        );
    }
}
