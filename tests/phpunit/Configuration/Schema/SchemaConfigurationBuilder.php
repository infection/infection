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

namespace Infection\Tests\Configuration\Schema;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\TestFrameworkTypes;

final class SchemaConfigurationBuilder
{
    /**
     * @param non-empty-string $pathname
     * @param array<string, mixed> $mutators
     * @param TestFrameworkTypes::*|null $testFramework
     * @param StaticAnalysisToolTypes::*|null $staticAnalysisTool
     */
    private function __construct(
        private string $pathname,
        private ?float $timeout,
        private Source $source,
        private Logs $logs,
        private ?string $tmpDir,
        private PhpUnit $phpUnit,
        private PhpStan $phpStan,
        private ?bool $ignoreMsiWithNoMutations,
        private ?float $minMsi,
        private ?float $minCoveredMsi,
        private ?bool $timeoutsAsEscaped,
        private ?int $maxTimeouts,
        private array $mutators,
        private ?string $testFramework,
        private ?string $bootstrap,
        private ?string $initialTestsPhpOptions,
        private ?string $testFrameworkExtraOptions,
        private ?string $staticAnalysisToolOptions,
        private string|int|null $threads,
        private ?string $staticAnalysisTool,
    ) {
    }

    public static function from(SchemaConfiguration $schema): self
    {
        return new self(
            pathname: $schema->pathname,
            timeout: $schema->timeout,
            source: $schema->source,
            logs: $schema->logs,
            tmpDir: $schema->tmpDir,
            phpUnit: $schema->phpUnit,
            phpStan: $schema->phpStan,
            ignoreMsiWithNoMutations: $schema->ignoreMsiWithNoMutations,
            minMsi: $schema->minMsi,
            minCoveredMsi: $schema->minCoveredMsi,
            timeoutsAsEscaped: $schema->timeoutsAsEscaped,
            maxTimeouts: $schema->maxTimeouts,
            mutators: $schema->mutators,
            testFramework: $schema->testFramework,
            bootstrap: $schema->bootstrap,
            initialTestsPhpOptions: $schema->initialTestsPhpOptions,
            testFrameworkExtraOptions: $schema->testFrameworkExtraOptions,
            staticAnalysisToolOptions: $schema->staticAnalysisToolOptions,
            threads: $schema->threads,
            staticAnalysisTool: $schema->staticAnalysisTool,
        );
    }

    public static function withMinimalTestData(): self
    {
        return new self(
            pathname: '/path/to/infection.json',
            timeout: null,
            source: new Source([], []),
            logs: Logs::createEmpty(),
            tmpDir: null,
            phpUnit: new PhpUnit(null, null),
            phpStan: new PhpStan(null, null),
            ignoreMsiWithNoMutations: null,
            minMsi: null,
            minCoveredMsi: null,
            timeoutsAsEscaped: null,
            maxTimeouts: null,
            mutators: [],
            testFramework: null,
            bootstrap: null,
            initialTestsPhpOptions: null,
            testFrameworkExtraOptions: null,
            staticAnalysisToolOptions: null,
            threads: null,
            staticAnalysisTool: null,
        );
    }

    public static function withCompleteTestData(): self
    {
        return new self(
            pathname: '/complete/path/infection.json',
            timeout: 10.0,
            source: new Source(['src', 'lib'], ['vendor', 'tests']),
            logs: new Logs(
                textLogFilePath: 'text.log',
                htmlLogFilePath: 'report.html',
                summaryLogFilePath: 'summary.log',
                jsonLogFilePath: 'json.log',
                gitlabLogFilePath: 'gitlab.log',
                debugLogFilePath: 'debug.log',
                perMutatorFilePath: 'mutator.log',
                useGitHubAnnotationsLogger: true,
                strykerConfig: StrykerConfig::forBadge('master'),
                summaryJsonLogFilePath: 'summary.json',
            ),
            tmpDir: '/tmp/infection',
            phpUnit: new PhpUnit('/config/phpunit', '/custom/phpunit'),
            phpStan: new PhpStan('/config/phpstan', '/custom/phpstan'),
            ignoreMsiWithNoMutations: true,
            minMsi: 80.0,
            minCoveredMsi: 90.0,
            timeoutsAsEscaped: true,
            maxTimeouts: 5,
            mutators: ['@default' => true],
            testFramework: TestFrameworkTypes::PHPUNIT,
            bootstrap: 'bootstrap.php',
            initialTestsPhpOptions: '-d memory_limit=1G',
            testFrameworkExtraOptions: '--verbose',
            staticAnalysisToolOptions: '--level=max',
            threads: 4,
            staticAnalysisTool: StaticAnalysisToolTypes::PHPSTAN,
        );
    }

    /**
     * @param non-empty-string $pathname
     */
    public function withPathname(string $pathname): self
    {
        $clone = clone $this;
        $clone->pathname = $pathname;

        return $clone;
    }

    public function withTimeout(?float $timeout): self
    {
        $clone = clone $this;
        $clone->timeout = $timeout;

        return $clone;
    }

    public function withSource(Source $source): self
    {
        $clone = clone $this;
        $clone->source = $source;

        return $clone;
    }

    public function withLogs(Logs $logs): self
    {
        $clone = clone $this;
        $clone->logs = $logs;

        return $clone;
    }

    public function withTmpDir(?string $tmpDir): self
    {
        $clone = clone $this;
        $clone->tmpDir = $tmpDir;

        return $clone;
    }

    public function withPhpUnit(PhpUnit $phpUnit): self
    {
        $clone = clone $this;
        $clone->phpUnit = $phpUnit;

        return $clone;
    }

    public function withPhpStan(PhpStan $phpStan): self
    {
        $clone = clone $this;
        $clone->phpStan = $phpStan;

        return $clone;
    }

    public function withIgnoreMsiWithNoMutations(?bool $ignoreMsiWithNoMutations): self
    {
        $clone = clone $this;
        $clone->ignoreMsiWithNoMutations = $ignoreMsiWithNoMutations;

        return $clone;
    }

    public function withMinMsi(?float $minMsi): self
    {
        $clone = clone $this;
        $clone->minMsi = $minMsi;

        return $clone;
    }

    public function withMinCoveredMsi(?float $minCoveredMsi): self
    {
        $clone = clone $this;
        $clone->minCoveredMsi = $minCoveredMsi;

        return $clone;
    }

    public function withTimeoutsAsEscaped(?bool $timeoutsAsEscaped): self
    {
        $clone = clone $this;
        $clone->timeoutsAsEscaped = $timeoutsAsEscaped;

        return $clone;
    }

    public function withMaxTimeouts(?int $maxTimeouts): self
    {
        $clone = clone $this;
        $clone->maxTimeouts = $maxTimeouts;

        return $clone;
    }

    /**
     * @param array<string, mixed> $mutators
     */
    public function withMutators(array $mutators): self
    {
        $clone = clone $this;
        $clone->mutators = $mutators;

        return $clone;
    }

    /**
     * @param TestFrameworkTypes::*|null $testFramework
     */
    public function withTestFramework(?string $testFramework): self
    {
        $clone = clone $this;
        $clone->testFramework = $testFramework;

        return $clone;
    }

    public function withBootstrap(?string $bootstrap): self
    {
        $clone = clone $this;
        $clone->bootstrap = $bootstrap;

        return $clone;
    }

    public function withInitialTestsPhpOptions(?string $initialTestsPhpOptions): self
    {
        $clone = clone $this;
        $clone->initialTestsPhpOptions = $initialTestsPhpOptions;

        return $clone;
    }

    public function withTestFrameworkExtraOptions(?string $testFrameworkExtraOptions): self
    {
        $clone = clone $this;
        $clone->testFrameworkExtraOptions = $testFrameworkExtraOptions;

        return $clone;
    }

    public function withStaticAnalysisToolOptions(?string $staticAnalysisToolOptions): self
    {
        $clone = clone $this;
        $clone->staticAnalysisToolOptions = $staticAnalysisToolOptions;

        return $clone;
    }

    public function withThreads(string|int|null $threads): self
    {
        $clone = clone $this;
        $clone->threads = $threads;

        return $clone;
    }

    /**
     * @param StaticAnalysisToolTypes::*|null $staticAnalysisTool
     */
    public function withStaticAnalysisTool(?string $staticAnalysisTool): self
    {
        $clone = clone $this;
        $clone->staticAnalysisTool = $staticAnalysisTool;

        return $clone;
    }

    public function build(): SchemaConfiguration
    {
        return new SchemaConfiguration(
            pathname: $this->pathname,
            timeout: $this->timeout,
            source: $this->source,
            logs: $this->logs,
            tmpDir: $this->tmpDir,
            phpUnit: $this->phpUnit,
            phpStan: $this->phpStan,
            ignoreMsiWithNoMutations: $this->ignoreMsiWithNoMutations,
            minMsi: $this->minMsi,
            minCoveredMsi: $this->minCoveredMsi,
            timeoutsAsEscaped: $this->timeoutsAsEscaped,
            maxTimeouts: $this->maxTimeouts,
            mutators: $this->mutators,
            testFramework: $this->testFramework,
            bootstrap: $this->bootstrap,
            initialTestsPhpOptions: $this->initialTestsPhpOptions,
            testFrameworkExtraOptions: $this->testFrameworkExtraOptions,
            staticAnalysisToolOptions: $this->staticAnalysisToolOptions,
            threads: $this->threads,
            staticAnalysisTool: $this->staticAnalysisTool,
        );
    }
}
