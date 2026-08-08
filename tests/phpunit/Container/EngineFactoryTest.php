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

namespace Infection\Tests\Container;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Container\Container;
use Infection\Container\EngineFactory;
use Infection\Engine;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Metrics\MaxTimeoutsChecker;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Mutation\MutationGenerator;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(EngineFactory::class)]
final class EngineFactoryTest extends TestCase
{
    public function test_it_creates_the_engine(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()->build();

        $container = $this->createContainerWithDoubles($configuration);
        $consoleOutput = new ConsoleOutput(new NullLogger());

        $engine = $container->getEngineFactory()->create($consoleOutput);

        $this->assertEquals(
            $this->createExpectedEngine($configuration, $container, $consoleOutput),
            $engine,
        );
    }

    private function createContainerWithDoubles(Configuration $configuration): Container
    {
        $container = Container::create();

        $container->inject(Configuration::class, $configuration);
        $container->inject(SyncEventDispatcher::class, new SyncEventDispatcher());

        foreach ([
            TestFrameworkAdapter::class,
            CoverageChecker::class,
            InitialTestsRunner::class,
            MemoryLimiter::class,
            MutationGenerator::class,
            MutationTestingRunner::class,
            MinMsiChecker::class,
            MaxTimeoutsChecker::class,
            MetricsCalculator::class,
            TestFrameworkExtraOptionsFilter::class,
            InitialStaticAnalysisRunner::class,
        ] as $id) {
            $container->inject($id, $this->createStub($id));
        }

        return $container;
    }

    private function createExpectedEngine(
        Configuration $configuration,
        Container $container,
        ConsoleOutput $consoleOutput,
    ): Engine {
        return new Engine(
            $configuration,
            $container->getTestFrameworkAdapter(),
            $container->getCoverageChecker(),
            $container->getEventDispatcher(),
            $container->getInitialTestsRunner(),
            $container->getMemoryLimiter(),
            $container->getMutationGenerator(),
            $container->getMutationTestingRunner(),
            $container->getMinMsiChecker(),
            $container->getMaxTimeoutsChecker(),
            $consoleOutput,
            $container->getMetricsCalculator(),
            $container->getTestFrameworkExtraOptionsFilter(),
            $container->getInitialStaticAnalysisRunner(),
        );
    }
}
