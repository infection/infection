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

namespace Infection\Tests\Telemetry\Attribute;

use Generator;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\Framework\InfectionVersion;
use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\DetectionStatus;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\Telemetry\Attribute\RunSpanAttributesProvider;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type Attributes from RunSpanAttributesProvider
 */
#[CoversClass(RunSpanAttributesProvider::class)]
final class RunSpanAttributesProviderTest extends TestCase
{
    public function test_it_provides_run_identity_attributes_from_the_configuration(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withProjectDirectory('/var/www/project')
            ->withProjectName('acme/package')
            ->withConfigPathname('/var/www/project/config/infection.json5')
            ->withThreadCount(8)
            ->withSkipInitialTests(true)
            ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
            ->withGitSha('0123456789abcdef')
            ->build();

        $infectionVersionMock = $this->createMock(InfectionVersion::class);
        $infectionVersionMock
            ->method('prettyVersion')
            ->willReturn('1.2.3');
        $testFrameworkAdapter = $this->createStub(TestFrameworkAdapter::class);
        $testFrameworkAdapter
            ->method('getVersion')
            ->willReturn('12.3.4');
        $staticAnalysisToolAdapter = $this->createStub(StaticAnalysisToolAdapter::class);
        $staticAnalysisToolAdapter
            ->method('getVersion')
            ->willReturn('2.1.17');

        $provider = new RunSpanAttributesProvider(
            $configuration,
            $infectionVersionMock,
            $testFrameworkAdapter,
            $staticAnalysisToolAdapter,
            new MetricsCalculator($configuration->msiPrecision, $configuration->timeoutsAsEscaped),
        );

        $expected = [
            'infection.project.name' => 'acme/package',
            'infection.project.dir' => '/var/www/project',
            'infection.config.path' => 'config/infection.json5',
            'infection.version' => '1.2.3',
            'infection.distribution' => 'source',
            'infection.git.sha' => '0123456789abcdef',
            'infection.thread.count' => 8,
            'infection.initial_tests.skipped' => true,
            'infection.initial_static_analysis.skipped' => false,
            'infection.test_framework.name' => 'phpunit',
            'infection.test_framework.version' => '12.3.4',
            'infection.static_analysis_tool.name' => 'phpstan',
            'infection.static_analysis_tool.version' => '2.1.17',
        ];

        $actual = $provider->provideInitialAttributes();

        $this->assertSame($expected, $actual);
    }

    public function test_it_omits_static_analysis_tool_attributes_when_static_analysis_is_disabled(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withStaticAnalysisTool(null)
            ->build();

        $infectionVersion = $this->createStub(InfectionVersion::class);
        $infectionVersion
            ->method('prettyVersion')
            ->willReturn('1.2.3');
        $testFrameworkAdapter = $this->createStub(TestFrameworkAdapter::class);
        $testFrameworkAdapter
            ->method('getVersion')
            ->willReturn('12.3.4');

        $provider = new RunSpanAttributesProvider(
            $configuration,
            $infectionVersion,
            $testFrameworkAdapter,
            null,
            new MetricsCalculator($configuration->msiPrecision, $configuration->timeoutsAsEscaped),
        );

        $actual = $provider->provideInitialAttributes();

        $this->assertSame('phpunit', $actual['infection.test_framework.name']);
        $this->assertSame('12.3.4', $actual['infection.test_framework.version']);
        $this->assertArrayNotHasKey('infection.static_analysis_tool.name', $actual);
        $this->assertArrayNotHasKey('infection.static_analysis_tool.version', $actual);
    }

    /**
     * @param list<DetectionStatus> $detectionStatuses
     * @param Attributes $expected
     */
    #[DataProvider('summaryAttributesProvider')]
    public function test_it_provides_summary_attributes(
        Configuration $configuration,
        array $detectionStatuses,
        int $sourceFileCount,
        int $mutationCount,
        int $evaluatedMutationCount,
        array $expected,
    ): void {
        $metricsCalculator = new MetricsCalculator(
            $configuration->msiPrecision,
            $configuration->timeoutsAsEscaped,
        );

        foreach ($detectionStatuses as $index => $status) {
            $metricsCalculator->collect(
                MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutantHash('mutation-' . $index)
                    ->withDetectionStatus($status)
                    ->build(),
            );
        }

        $provider = new RunSpanAttributesProvider(
            $configuration,
            $this->createStub(InfectionVersion::class),
            $this->createStub(TestFrameworkAdapter::class),
            null,
            $metricsCalculator,
        );

        $actual = $provider->provideSummaryAttributes(
            $sourceFileCount,
            $mutationCount,
            $evaluatedMutationCount,
        );

        $this->assertSame($expected, $actual);
    }

    public static function summaryAttributesProvider(): Generator
    {
        yield 'zero-valued counts and default thresholds' => [
            ConfigurationBuilder::withMinimalTestData()->build(),
            [],
            0,
            0,
            0,
            [
                'infection.source_file.count' => 0,
                'infection.mutation.count' => 0,
                'infection.mutation.suppressed.count' => 0,
                'infection.mutation.evaluated.count' => 0,
                'infection.mutation.killed_by_tests.count' => 0,
                'infection.mutation.killed_by_static_analysis.count' => 0,
                'infection.mutation.escaped.count' => 0,
                'infection.mutation.error.count' => 0,
                'infection.mutation.timed_out.count' => 0,
                'infection.mutation.skipped.count' => 0,
                'infection.mutation.syntax_error.count' => 0,
                'infection.mutation.not_covered.count' => 0,
                'infection.mutation.ignored.count' => 0,
                'infection.msi' => 0.0,
                'infection.covered_msi' => 0.0,
                'infection.msi.threshold' => 0.0,
                'infection.covered_msi.threshold' => 0.0,
            ],
        ];

        yield 'counts scores thresholds and suppression invariants' => [
            ConfigurationBuilder::withMinimalTestData()
                ->withMinMsi(72.3)
                ->withMinCoveredMsi(81.5)
                ->build(),
            [
                DetectionStatus::KILLED_BY_TESTS,
                DetectionStatus::KILLED_BY_TESTS,
                DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
                DetectionStatus::ERROR,
                DetectionStatus::SYNTAX_ERROR,
                DetectionStatus::ESCAPED,
                DetectionStatus::TIMED_OUT,
                DetectionStatus::SKIPPED,
                DetectionStatus::NOT_COVERED,
                DetectionStatus::IGNORED,
            ],
            3,
            12,
            10,
            [
                'infection.source_file.count' => 3,
                'infection.mutation.count' => 12,
                'infection.mutation.suppressed.count' => 2,
                'infection.mutation.evaluated.count' => 10,
                'infection.mutation.killed_by_tests.count' => 2,
                'infection.mutation.killed_by_static_analysis.count' => 1,
                'infection.mutation.escaped.count' => 1,
                'infection.mutation.error.count' => 1,
                'infection.mutation.timed_out.count' => 1,
                'infection.mutation.skipped.count' => 1,
                'infection.mutation.syntax_error.count' => 1,
                'infection.mutation.not_covered.count' => 1,
                'infection.mutation.ignored.count' => 1,
                'infection.msi' => 75.0,
                'infection.covered_msi' => 85.71,
                'infection.msi.threshold' => 72.3,
                'infection.covered_msi.threshold' => 81.5,
            ],
        ];
    }
}
