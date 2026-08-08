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

namespace Infection\Tests\Container\Builder;

use Infection\Container\Builder\InitialStaticAnalysisRunnerBuilder;
use Infection\Container\Container;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Process\Factory\InitialStaticAnalysisProcessFactory;
use Infection\Process\Runner\InitialStaticAnalysisProcessRunner;
use Infection\Process\Runner\NullInitialStaticAnalysisRunner;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\Tests\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitialStaticAnalysisRunnerBuilder::class)]
final class InitialStaticAnalysisRunnerBuilderTest extends TestCase
{
    public function test_it_builds_a_null_runner_when_static_analysis_is_not_enabled(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withStaticAnalysisTool(null)
            ->build();

        $runner = (new InitialStaticAnalysisRunnerBuilder($configuration, Container::create()))->build();

        $this->assertInstanceOf(NullInitialStaticAnalysisRunner::class, $runner);
    }

    public function test_it_builds_a_process_runner_when_static_analysis_is_enabled(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
            ->build();

        $processFactory = $this->createStub(InitialStaticAnalysisProcessFactory::class);
        $eventDispatcher = new SyncEventDispatcher();
        $staticAnalysisToolAdapter = $this->createStub(StaticAnalysisToolAdapter::class);

        $container = Container::create();
        $container->inject(InitialStaticAnalysisProcessFactory::class, $processFactory);
        $container->inject(SyncEventDispatcher::class, $eventDispatcher);
        $container->inject(StaticAnalysisToolAdapter::class, $staticAnalysisToolAdapter);

        $runner = (new InitialStaticAnalysisRunnerBuilder($configuration, $container))->build();

        $this->assertEquals(
            new InitialStaticAnalysisProcessRunner($processFactory, $eventDispatcher, $staticAnalysisToolAdapter),
            $runner,
        );
    }
}
