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

namespace Infection\Tests\TestFramework;

use Infection\Console\ConsoleOutput;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\LegacyTestFrameworkBridge;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Fixtures\TestFramework\FakeAwareAdapter;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(LegacyTestFrameworkBridge::class)]
final class LegacyTestFrameworkBridgeTest extends TestCase
{
    public function test_it_checks_existing_coverage_when_initial_tests_are_skipped(): void
    {
        $consoleOutput = $this->createMock(ConsoleOutput::class);
        $consoleOutput
            ->expects($this->once())
            ->method('logSkippingInitialTests')
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageExists')
        ;

        $this->createBridge(
            consoleOutput: $consoleOutput,
            coverageChecker: $coverageChecker,
            skipInitialTests: true,
        )->checkRequirements();
    }

    public function test_it_executes_initial_run_and_reports_memory_usage(): void
    {
        $process = $this->createSuccessfulInitialRunProcess('output');

        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with('', [''], false)
            ->willReturn($process)
        ;

        $coverageChecker = $this->createMock(CoverageChecker::class);
        $coverageChecker
            ->expects($this->once())
            ->method('checkCoverageHasBeenGenerated')
            ->with('/tmp/phpunit', 'output')
        ;

        $results = $this->createBridge(
            adapter: new FakeAwareAdapter(42.0),
            coverageChecker: $coverageChecker,
            initialTestsRunner: $initialTestsRunner,
        )->executeInitialRun();

        $this->assertSame(42.0, $results->memoryUsage);
    }

    public function test_it_normalizes_unknown_legacy_memory_usage(): void
    {
        $initialTestsRunner = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunner
            ->method('run')
            ->willReturn($this->createSuccessfulInitialRunProcess('output'))
        ;

        $results = $this->createBridge(
            adapter: new FakeAwareAdapter(-1.0),
            initialTestsRunner: $initialTestsRunner,
        )->executeInitialRun();

        $this->assertNull($results->memoryUsage);
    }

    public function test_it_delegates_mutant_evaluation_to_the_legacy_process_factory(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $processContainer = $this->createStub(MutantProcessContainer::class);

        $processFactory = $this->createMock(MutantProcessContainerFactory::class);
        $processFactory
            ->expects($this->once())
            ->method('create')
            ->with($mutant, '')
            ->willReturn($processContainer)
        ;

        $actual = $this->createBridge(processFactory: $processFactory)->test($mutant);

        $this->assertSame($processContainer, $actual);
    }

    private function createBridge(
        ?FakeAwareAdapter $adapter = null,
        ?ConsoleOutput $consoleOutput = null,
        ?CoverageChecker $coverageChecker = null,
        ?InitialTestsRunner $initialTestsRunner = null,
        ?MutantProcessContainerFactory $processFactory = null,
        bool $skipInitialTests = false,
    ): LegacyTestFrameworkBridge {
        return new LegacyTestFrameworkBridge(
            $adapter ?? new FakeAwareAdapter(1.0),
            $consoleOutput ?? $this->createStub(ConsoleOutput::class),
            $coverageChecker ?? $this->createStub(CoverageChecker::class),
            $initialTestsRunner ?? $this->createStub(InitialTestsRunner::class),
            ConfigurationBuilder::withMinimalTestData()
                ->withSkipInitialTests($skipInitialTests)
                ->build(),
            $processFactory ?? $this->createStub(MutantProcessContainerFactory::class),
            $this->createStub(TestFrameworkExtraOptionsFilter::class),
        );
    }

    private function createSuccessfulInitialRunProcess(string $output): Process
    {
        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true)
        ;
        $process
            ->method('getOutput')
            ->willReturn($output)
        ;
        $process
            ->method('getCommandLine')
            ->willReturn('/tmp/phpunit')
        ;

        return $process;
    }
}
