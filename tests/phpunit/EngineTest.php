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

namespace Infection\Tests;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Engine;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Mutation\MutationGenerator;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use PHPUnit\Framework\TestCase;
use TypeError;

final class EngineTest extends TestCase
{
    public function test_it_runs(): void
    {
        $config = $this->createMock(Configuration::class);
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $coverageChecker = $this->createMock(CoverageChecker::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $memoryLimiter = $this->createMock(MemoryLimiter::class);
        $mutationGenerator = $this->createMock(MutationGenerator::class);
        $mutationTestingRunner = $this->createMock(MutationTestingRunner::class);
        $minMsiChecker = $this->createMock(MinMsiChecker::class);
        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $metricsCalculator = $this->createMock(MetricsCalculator::class);
        $testFrameworkExtraOptionsFilter = $this->createMock(TestFrameworkExtraOptionsFilter::class);

        $engine = new Engine($config, $adapter, $coverageChecker, $eventDispatcher, $initialTestsRunner, $memoryLimiter, $mutationGenerator, $mutationTestingRunner, $minMsiChecker, $consoleOutput, $metricsCalculator, $testFrameworkExtraOptionsFilter);

        $this->expectException(TypeError::class);
        $engine->execute();
    }
}
