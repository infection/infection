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

namespace Infection\Configuration;

use function array_fill_keys;
use function dirname;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\FileSystem\SourceFileCollector;
use Infection\FileSystem\TmpDirProvider;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\TestFramework\Coverage\XMLLineCodeCoverage;
use Infection\TestFramework\PhpSpec\PhpSpecExtraOptions;
use Infection\TestFramework\PhpUnit\PhpUnitExtraOptions;
use Infection\TestFramework\TestFrameworkExtraOptions;
use Infection\TestFramework\TestFrameworkTypes;
use function Safe\sprintf;
use function sys_get_temp_dir;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

/**
 * @internal
 * @final
 */
class ConfigurationFactory
{
    /**
     * Default allowed timeout (on a test basis) in seconds
     */
    private const DEFAULT_TIMEOUT = 10;

    private const TEST_FRAMEWORK_COVERAGE_DIRECTORY = [
        TestFrameworkTypes::PHPUNIT => XMLLineCodeCoverage::PHP_UNIT_COVERAGE_DIR,
        TestFrameworkTypes::PHPSPEC => XMLLineCodeCoverage::PHP_SPEC_COVERAGE_DIR,
        TestFrameworkTypes::CODECEPTION => XMLLineCodeCoverage::CODECEPTION_COVERAGE_DIR,
    ];

    private $tmpDirProvider;
    private $mutatorFactory;
    private $mutatorParser;
    private $sourceFileCollector;

    public function __construct(
        TmpDirProvider $tmpDirProvider,
        MutatorFactory $mutatorFactory,
        MutatorParser $mutatorParser,
        SourceFileCollector $sourceFileCollector
    ) {
        $this->tmpDirProvider = $tmpDirProvider;
        $this->mutatorFactory = $mutatorFactory;
        $this->mutatorParser = $mutatorParser;
        $this->sourceFileCollector = $sourceFileCollector;
    }

    public function create(
        SchemaConfiguration $schema,
        ?string $existingCoveragePath,
        ?string $initialTestsPhpOptions,
        string $logVerbosity,
        bool $debug,
        bool $onlyCovered,
        string $formatter,
        bool $noProgress,
        bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        bool $showMutations,
        ?float $minCoveredMsi,
        string $mutatorsInput,
        ?string $testFramework,
        ?string $testFrameworkExtraOptions,
        string $filter
    ): Configuration {
        $configDir = dirname($schema->getFile());

        $schemaMutators = $schema->getMutators();

        $namespacedTmpDir = $this->retrieveTmpDir($schema, $configDir);

        $testFramework = $testFramework ?? $schema->getTestFramework() ?? TestFrameworkTypes::PHPUNIT;

        $skipCoverage = $existingCoveragePath !== null;

        $existingCoverageBasePath = self::retrieveExistingCoverageBasePath(
            $existingCoveragePath,
            $configDir,
            $namespacedTmpDir
        );

        return new Configuration(
            $schema->getTimeout() ?? self::DEFAULT_TIMEOUT,
            $schema->getSource()->getDirectories(),
            $this->sourceFileCollector->collectFiles(
                $schema->getSource()->getDirectories(),
                $schema->getSource()->getExcludes(),
                $filter
            ),
            $schema->getLogs(),
            $logVerbosity,
            $namespacedTmpDir,
            $this->retrievePhpUnit($schema, $configDir),
            $this->mutatorFactory->create(
                $this->retrieveMutators(
                    $schemaMutators === []
                        ? ['@default' => true]
                        : $schemaMutators,
                    $mutatorsInput
                )
            ),
            $testFramework,
            $schema->getBootstrap(),
            $initialTestsPhpOptions ?? $schema->getInitialTestsPhpOptions(),
            self::retrieveTestFrameworkExtraOptions($testFrameworkExtraOptions, $schema, $testFramework),
            self::retrieveExistingCoveragePath($existingCoverageBasePath, $testFramework),
            $skipCoverage,
            $debug,
            $onlyCovered,
            $formatter,
            $noProgress,
            $ignoreMsiWithNoMutations,
            $minMsi,
            $showMutations,
            $minCoveredMsi
        );
    }

    private function retrieveTmpDir(
        SchemaConfiguration $schema,
        string $configDir
    ): string {
        $tmpDir = (string) $schema->getTmpDir();

        if ('' === $tmpDir) {
            $tmpDir = sys_get_temp_dir();
        } elseif (!Path::isAbsolute($tmpDir)) {
            $tmpDir = sprintf('%s/%s', $configDir, $tmpDir);
        }

        return $this->tmpDirProvider->providePath($tmpDir);
    }

    private function retrievePhpUnit(SchemaConfiguration $schema, string $configDir): PhpUnit
    {
        $phpUnit = clone $schema->getPhpUnit();

        $phpUnitConfigDir = $phpUnit->getConfigDir();

        if (null === $phpUnitConfigDir) {
            $phpUnit->setConfigDir($configDir);
        } elseif (!Path::isAbsolute($phpUnitConfigDir)) {
            $phpUnit->setConfigDir(sprintf(
                '%s/%s', $configDir, $phpUnitConfigDir
            ));
        }

        return $phpUnit;
    }

    private static function retrieveExistingCoveragePath(
        string $existingCoverageBasePath,
        string $testFramework
    ): string {
        Assert::keyExists(self::TEST_FRAMEWORK_COVERAGE_DIRECTORY, $testFramework);

        return sprintf(
            '%s/%s',
            $existingCoverageBasePath,
            self::TEST_FRAMEWORK_COVERAGE_DIRECTORY[$testFramework]
        );
    }

    private static function retrieveExistingCoverageBasePath(
        ?string $existingCoveragePath,
        string $configDir,
        string $tmpDir
    ): string {
        Assert::nullOrStringNotEmpty($existingCoveragePath);

        if ($existingCoveragePath === null) {
            return $tmpDir;
        }

        if (Path::isAbsolute($existingCoveragePath)) {
            return $existingCoveragePath;
        }

        return sprintf('%s/%s', $configDir, $existingCoveragePath);
    }

    private function retrieveMutators(array $schemaMutators, string $mutatorsInput): array
    {
        $parsedMutatorsInput = $this->mutatorParser->parse($mutatorsInput);

        if ([] === $parsedMutatorsInput) {
            return $schemaMutators;
        }

        return array_fill_keys($parsedMutatorsInput, true);
    }

    private static function retrieveTestFrameworkExtraOptions(
        ?string $testFrameworkExtraOptions,
        SchemaConfiguration $schema,
        string $testFramework
    ): TestFrameworkExtraOptions {
        $extraOptions = $testFrameworkExtraOptions ?? $schema->getTestFrameworkExtraOptions();

        return TestFrameworkTypes::PHPUNIT === $testFramework
            ? new PhpUnitExtraOptions($extraOptions)
            : new PhpSpecExtraOptions($extraOptions)
        ;
    }
}
