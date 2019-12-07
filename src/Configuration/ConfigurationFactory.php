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
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\FileSystem\TmpDirProvider;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use function sprintf;
use function sys_get_temp_dir;
use Webmozart\PathUtil\Path;

/**
 * @internal
 * @final
 */
class ConfigurationFactory
{
    private $tmpDirProvider;
    private $mutatorFactory;
    private $mutatorParser;

    public function __construct(
        TmpDirProvider $tmpDirProvider,
        MutatorFactory $mutatorFactory,
        MutatorParser $mutatorParser
    ) {
        $this->tmpDirProvider = $tmpDirProvider;
        $this->mutatorFactory = $mutatorFactory;
        $this->mutatorParser = $mutatorParser;
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
        ?string $testFrameworkOptions,
        string $filter
    ): Configuration {
        $configDir = dirname($schema->getFile());

        $tmpDir = (string) $schema->getTmpDir();

        if ('' === $tmpDir) {
            $tmpDir = sys_get_temp_dir();
        } elseif (!Path::isAbsolute($tmpDir)) {
            $tmpDir = sprintf('%s/%s', $configDir, $tmpDir);
        }

        $phpUnitConfigDir = $schema->getPhpUnit()->getConfigDir();

        if (null === $phpUnitConfigDir) {
            $schema->getPhpUnit()->setConfigDir($configDir);
        } elseif (!Path::isAbsolute($phpUnitConfigDir)) {
            $schema->getPhpUnit()->setConfigDir(sprintf(
                '%s/%s', $configDir, $phpUnitConfigDir
            ));
        }

        $schemaMutators = $schema->getMutators();

        return new Configuration(
            $schema->getTimeout() ?? 10,
            $schema->getSource(),
            $schema->getLogs(),
            $logVerbosity,
            $this->tmpDirProvider->providePath($tmpDir),
            $schema->getPhpUnit(),
            $this->mutatorFactory->create(
                $this->retrieveMutators(
                    $schemaMutators === []
                        ? ['@default' => true]
                        : $schemaMutators,
                    $mutatorsInput
                )
            ),
            $testFramework ?? $schema->getTestFramework(),
            $schema->getBootstrap(),
            $initialTestsPhpOptions ?? $schema->getInitialTestsPhpOptions(),
            $testFrameworkOptions ?? $schema->getTestFrameworkOptions(),
            $existingCoveragePath,
            $debug,
            $onlyCovered,
            $formatter,
            $noProgress,
            $ignoreMsiWithNoMutations,
            $minMsi,
            $showMutations,
            $minCoveredMsi,
            $filter
        );
    }

    private function retrieveMutators(array $schemaMutators, string $mutatorsInput): array
    {
        $parsedMutatorsInput = $this->mutatorParser->parse($mutatorsInput);

        if ([] === $parsedMutatorsInput) {
            return $schemaMutators;
        }

        return array_fill_keys($parsedMutatorsInput, true);
    }
}
