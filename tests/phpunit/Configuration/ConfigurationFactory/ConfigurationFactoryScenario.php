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

namespace Infection\Tests\Configuration\ConfigurationFactory;

use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Configuration\SourceFilter\SourceFilter;
use Infection\Mutator\Mutator;
use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Configuration\Entry\LogsBuilder;
use Infection\Tests\Configuration\Schema\SchemaConfigurationBuilder;

final class ConfigurationFactoryScenario
{
    public function __construct(
        public bool $ciDetected,
        public bool $githubActionsDetected,
        public SchemaConfigurationBuilder $schemaBuilder,
        public ConfigurationFactoryInputBuilder $inputBuilder,
        public Configuration $expected,
    ) {
    }

    public static function create(
        bool $ciDetected,
        bool $githubActionsDetected,
        SchemaConfigurationBuilder $schemaBuilder,
        ConfigurationFactoryInputBuilder $inputBuilder,
        Configuration $expected,
    ): self {
        return new self(
            $ciDetected,
            $githubActionsDetected,
            $schemaBuilder,
            $inputBuilder,
            $expected,
        );
    }

    public function withCiDetected(bool $ciDetected): self
    {
        $clone = clone $this;
        $clone->ciDetected = $ciDetected;

        return $clone;
    }

    public function withGithubActionsDetected(bool $githubActionsDetected): self
    {
        $clone = clone $this;
        $clone->githubActionsDetected = $githubActionsDetected;

        return $clone;
    }

    public function withSchema(SchemaConfigurationBuilder $schemaBuilder): self
    {
        $clone = clone $this;
        $clone->schemaBuilder = $schemaBuilder;

        return $clone;
    }

    public function withInput(ConfigurationFactoryInputBuilder $builder): self
    {
        $clone = clone $this;
        $clone->inputBuilder = $builder;

        return $clone;
    }

    public function withExpected(Configuration $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }

    public function forValueForTextLogFilePath(
        ?string $textFileLogPathInConfig,
        ?string $textFileLogPathFromCliOption,
        ?string $expectedTextFileLogPath,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withLogs(
                        LogsBuilder::withMinimalTestData()
                            ->withTextLogFilePath($textFileLogPathInConfig)
                            ->build(),
                    ),
            )
            ->withInput(
                $this->inputBuilder
                    ->withTextLogFilePath($textFileLogPathFromCliOption),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withLogs(
                        LogsBuilder::from($this->expected->logs)
                            ->withTextLogFilePath($expectedTextFileLogPath)
                            ->build(),
                    )
                    ->build(),
            );
    }

    public function forValueForHtmlLogFilePath(
        ?string $htmlFileLogPathInConfig,
        ?string $htmlFileLogPathFromCliOption,
        ?string $expectedHtmlFileLogPath,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withLogs(
                        LogsBuilder::withMinimalTestData()
                            ->withHtmlLogFilePath($htmlFileLogPathInConfig)
                            ->build(),
                    ),
            )
            ->withInput(
                $this->inputBuilder
                    ->withHtmlLogFilePath($htmlFileLogPathFromCliOption),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withLogs(
                        LogsBuilder::from($this->expected->logs)
                            ->withHtmlLogFilePath($expectedHtmlFileLogPath)
                            ->build(),
                    )
                    ->build(),
            );
    }

    public function forValueForGitlabLogger(
        ?string $gitlabFileLogPathInConfig,
        ?string $gitlabFileLogPathFromCliOption,
        ?string $expectedGitlabFileLogPath,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withLogs(
                        LogsBuilder::withMinimalTestData()
                            ->withGitlabLogFilePath($gitlabFileLogPathInConfig)
                            ->build(),
                    ),
            )
            ->withInput(
                $this->inputBuilder
                    ->withGitlabLogFilePath($gitlabFileLogPathFromCliOption),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withLogs(
                        LogsBuilder::from($this->expected->logs)
                            ->withGitlabLogFilePath($expectedGitlabFileLogPath)
                            ->build(),
                    )
                    ->build(),
            );
    }

    public function forValueForTimeout(
        ?float $schemaTimeout,
        float $expectedTimeout,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withTimeout($schemaTimeout),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withTimeout($expectedTimeout)
                    ->build(),
            );
    }

    public function forValueForTmpDir(
        ?string $configTmpDir,
        string $expectedTmpDir,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withTmpDir($configTmpDir),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withTmpDir($expectedTmpDir)
                    ->withCoveragePath($expectedTmpDir)
                    ->build(),
            );
    }

    public function forValueForCoveragePath(
        ?string $existingCoveragePath,
        bool $expectedSkipCoverage,
        string $expectedCoveragePath,
    ): self {
        return $this
            ->withInput(
                $this->inputBuilder
                    ->withExistingCoveragePath($existingCoveragePath),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withCoveragePath($expectedCoveragePath)
                    ->withSkipCoverage($expectedSkipCoverage)
                    ->build(),
            );
    }

    public function forValueForPhpUnitConfigDir(
        ?string $phpUnitConfigDir,
        ?string $expectedPhpUnitConfigDir,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withPhpUnit(new PhpUnit($phpUnitConfigDir, null)),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withPhpUnit(new PhpUnit($expectedPhpUnitConfigDir, null))
                    ->build(),
            );
    }

    public function forValueForNoProgress(
        bool $ciDetected,
        bool $noProgress,
        bool $expectedNoProgress,
    ): self {
        return $this
            ->withCiDetected($ciDetected)
            ->withInput(
                $this->inputBuilder
                    ->withNoProgress($noProgress),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withNoProgress($expectedNoProgress)
                    ->build(),
            );
    }

    public function forValueForGithubActionsDetected(
        ?bool $inputUseGitHubAnnotationsLogger,
        bool $githubActionsDetected,
        bool $useGitHubAnnotationsLogger,
    ): self {
        return $this
            ->withGithubActionsDetected($githubActionsDetected)
            ->withSchema(
                SchemaConfigurationBuilder::from($this->schemaBuilder->build())
                    ->withLogs(Logs::createEmpty()),
            )
            ->withInput(
                $this->inputBuilder
                    ->withUseGitHubLogger($inputUseGitHubAnnotationsLogger),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withLogs(
                        LogsBuilder::from($this->expected->logs)
                            ->withUseGitHubAnnotationsLogger($useGitHubAnnotationsLogger)
                            ->build(),
                    )
                    ->build(),
            );
    }

    public function forValueForIgnoreMsiWithNoMutations(
        ?bool $ignoreMsiWithNoMutationsFromSchemaConfiguration,
        ?bool $ignoreMsiWithNoMutationsFromInput,
        bool $expectedIgnoreMsiWithNoMutations,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withIgnoreMsiWithNoMutations($ignoreMsiWithNoMutationsFromSchemaConfiguration),
            )
            ->withInput(
                $this->inputBuilder
                    ->withIgnoreMsiWithNoMutations($ignoreMsiWithNoMutationsFromInput),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withIgnoreMsiWithNoMutations($expectedIgnoreMsiWithNoMutations)
                    ->build(),
            );
    }

    public function forValueForMinMsi(
        ?float $minMsiFromSchemaConfiguration,
        ?float $minMsiFromInput,
        ?float $expectedMinMsi,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withMinMsi($minMsiFromSchemaConfiguration),
            )
            ->withInput(
                $this->inputBuilder
                    ->withMinMsi($minMsiFromInput),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withMinMsi($expectedMinMsi)
                    ->build(),
            );
    }

    public function forValueForMinCoveredMsi(
        ?float $minCoveredMsiFromSchemaConfiguration,
        ?float $minCoveredMsiFromInput,
        ?float $expectedMinCoveredMsi,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withMinCoveredMsi($minCoveredMsiFromSchemaConfiguration),
            )
            ->withInput(
                $this->inputBuilder
                    ->withMinCoveredMsi($minCoveredMsiFromInput),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withMinCoveredMsi($expectedMinCoveredMsi)
                    ->build(),
            );
    }

    /**
     * @param TestFrameworkTypes::*|null $configTestFramework
     * @param TestFrameworkTypes::*|null $inputTestFramework
     * @param TestFrameworkTypes::* $expectedTestFramework
     */
    public function forValueForTestFramework(
        ?string $configTestFramework,
        ?string $inputTestFramework,
        string $expectedTestFramework,
        string $expectedTestFrameworkExtraOptions,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withTestFramework($configTestFramework),
            )
            ->withInput(
                $this->inputBuilder
                    ->withTestFramework($inputTestFramework),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withTestFramework($expectedTestFramework)
                    ->withTestFrameworkExtraOptions($expectedTestFrameworkExtraOptions)
                    ->build(),
            );
    }

    /**
     * @param StaticAnalysisToolTypes::*|null $configStaticAnalysisTool
     * @param StaticAnalysisToolTypes::*|null $inputStaticAnalysisTool
     * @param StaticAnalysisToolTypes::*|null $expectedStaticAnalysisTool
     */
    public function forValueForStaticAnalysisTool(
        ?string $configStaticAnalysisTool,
        ?string $inputStaticAnalysisTool,
        ?string $expectedStaticAnalysisTool,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withTestFramework(TestFrameworkTypes::PHPUNIT)
                    ->withStaticAnalysisTool($configStaticAnalysisTool),
            )
            ->withInput(
                $this->inputBuilder
                    ->withStaticAnalysisTool($inputStaticAnalysisTool),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withStaticAnalysisTool($expectedStaticAnalysisTool)
                    ->build(),
            );
    }

    public function forValueForInitialTestsPhpOptions(
        ?string $configInitialTestsPhpOptions,
        ?string $inputInitialTestsPhpOptions,
        ?string $expectedInitialTestPhpOptions,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withInitialTestsPhpOptions($configInitialTestsPhpOptions),
            )
            ->withInput(
                $this->inputBuilder
                    ->withInitialTestsPhpOptions($inputInitialTestsPhpOptions),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withInitialTestsPhpOptions($expectedInitialTestPhpOptions)
                    ->build(),
            );
    }

    /**
     * @param TestFrameworkTypes::* $configTestFramework
     */
    public function forValueForTestFrameworkExtraOptions(
        string $configTestFramework,
        ?string $configTestFrameworkExtraOptions,
        ?string $inputTestFrameworkExtraOptions,
        string $expectedTestFrameworkExtraOptions,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withTestFramework($configTestFramework)
                    ->withTestFrameworkExtraOptions($configTestFrameworkExtraOptions),
            )
            ->withInput(
                $this->inputBuilder
                    ->withTestFrameworkExtraOptions($inputTestFrameworkExtraOptions),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withTestFramework($configTestFramework)
                    ->withTestFrameworkExtraOptions($expectedTestFrameworkExtraOptions)
                    ->build(),
            );
    }

    public function forValueForStaticAnalysisToolOptions(
        ?string $configStaticAnalysisToolOptions,
        ?string $inputStaticAnalysisToolOptions,
        ?string $expectedStaticAnalysisToolOptions,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withStaticAnalysisToolOptions($configStaticAnalysisToolOptions),
            )
            ->withInput(
                $this->inputBuilder
                    ->withStaticAnalysisToolOptions($inputStaticAnalysisToolOptions),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withStaticAnalysisToolOptions($expectedStaticAnalysisToolOptions)
                    ->build(),
            );
    }

    /**
     * @param TestFrameworkTypes::* $configTestFramework
     */
    public function forValueForTestFrameworkKey(
        string $configTestFramework,
        string $inputTestFrameworkExtraOptions,
        string $expectedTestFrameworkExtraOptions,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withTestFramework($configTestFramework),
            )
            ->withInput(
                $this->inputBuilder
                    ->withTestFrameworkExtraOptions($inputTestFrameworkExtraOptions),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withTestFramework($configTestFramework)
                    ->withTestFrameworkExtraOptions($expectedTestFrameworkExtraOptions)
                    ->build(),
            );
    }

    /**
     * @param array<string, mixed> $configMutators
     * @param array<string, Mutator> $expectedMutators
     * @param array<string, array<int, string>> $expectedIgnoreSourceCodeMutatorsMap
     */
    public function forValueForMutators(
        array $configMutators,
        string $inputMutators,
        bool $useNoopMutators,
        array $expectedMutators,
        array $expectedIgnoreSourceCodeMutatorsMap = [],
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withMutators($configMutators),
            )
            ->withInput(
                $this->inputBuilder
                    ->withMutatorsInput($inputMutators)
                    ->withUseNoopMutators($useNoopMutators),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withMutators($expectedMutators)
                    ->withIgnoreSourceCodeMutatorsMap($expectedIgnoreSourceCodeMutatorsMap)
                    ->build(),
            );
    }

    /**
     * @param array<string, mixed> $configMutators
     * @param array<string, array<int, string>> $expectedIgnoreSourceCodeMutatorsMap
     */
    public function forValueForIgnoreSourceCodeByRegex(
        array $configMutators,
        array $expectedIgnoreSourceCodeMutatorsMap,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withMutators($configMutators),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withMutators([
                        'MethodCallRemoval' => new MethodCallRemoval(),
                    ])
                    ->withIgnoreSourceCodeMutatorsMap($expectedIgnoreSourceCodeMutatorsMap)
                    ->build(),
            );
    }

    public function forSourceFilter(
        PlainFilter|IncompleteGitDiffFilter|null $sourceFilter,
        ?SourceFilter $expectedSourceFilter,
    ): self {
        return $this
            ->withInput(
                $this->inputBuilder
                ->withSourceFilter($sourceFilter),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withSourceFilter($expectedSourceFilter)
                    ->build(),
            );
    }

    public function forValueForTimeoutsAsEscaped(
        ?bool $timeoutsAsEscapedFromSchemaConfiguration,
        bool $timeoutsAsEscapedFromInput,
        bool $expectedTimeoutsAsEscaped,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withTimeoutsAsEscaped($timeoutsAsEscapedFromSchemaConfiguration),
            )
            ->withInput(
                $this->inputBuilder
                    ->withTimeoutsAsEscaped($timeoutsAsEscapedFromInput),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withTimeoutsAsEscaped($expectedTimeoutsAsEscaped)
                    ->build(),
            );
    }

    public function forValueForMaxTimeouts(
        ?int $maxTimeoutsFromSchemaConfiguration,
        ?int $maxTimeoutsFromInput,
        ?int $expectedMaxTimeouts,
    ): self {
        return $this
            ->withSchema(
                $this->schemaBuilder
                    ->withMaxTimeouts($maxTimeoutsFromSchemaConfiguration),
            )
            ->withInput(
                $this->inputBuilder
                    ->withMaxTimeouts($maxTimeoutsFromInput),
            )
            ->withExpected(
                ConfigurationBuilder::from($this->expected)
                    ->withMaxTimeouts($expectedMaxTimeouts)
                    ->build(),
            );
    }
}
