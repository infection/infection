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

namespace Infection\TestFramework;

use function array_filter;
use function array_map;
use function implode;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use Infection\Configuration\Configuration;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\FileSystem\SourceFileFilter;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\PhpUnit\Adapter\PestAdapterFactory;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapterFactory;
use InvalidArgumentException;
use function is_a;
use function iterator_to_array;
use function Safe\sprintf;
use SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Factory
{
    private string $tmpDir;
    private string $projectDir;
    private TestFrameworkConfigLocatorInterface $configLocator;
    private TestFrameworkFinder $testFrameworkFinder;
    private string $jUnitFilePath;
    private Configuration $infectionConfig;
    private SourceFileFilter $sourceFileFilter;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $installedExtensions;

    /**
     * @param array<string, array<string, mixed>> $installedExtensions
     */
    public function __construct(
        string $tmpDir,
        string $projectDir,
        TestFrameworkConfigLocatorInterface $configLocator,
        TestFrameworkFinder $testFrameworkFinder,
        string $jUnitFilePath,
        Configuration $infectionConfig,
        SourceFileFilter $sourceFileFilter,
        array $installedExtensions
    ) {
        $this->tmpDir = $tmpDir;
        $this->configLocator = $configLocator;
        $this->projectDir = $projectDir;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->infectionConfig = $infectionConfig;
        $this->testFrameworkFinder = $testFrameworkFinder;
        $this->sourceFileFilter = $sourceFileFilter;
        $this->installedExtensions = $installedExtensions;
    }

    public function create(string $adapterName, bool $skipCoverage): TestFrameworkAdapter
    {
        $filteredSourceFilesToMutate = $this->getFilteredSourceFilesToMutate();

        if ($adapterName === TestFrameworkTypes::PHPUNIT) {
            $phpUnitConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);

            return PhpUnitAdapterFactory::create(
                $this->testFrameworkFinder->find(
                    TestFrameworkTypes::PHPUNIT,
                    (string) $this->infectionConfig->getPhpUnit()->getCustomPath()
                ),
                $this->tmpDir,
                $phpUnitConfigPath,
                (string) $this->infectionConfig->getPhpUnit()->getConfigDir(),
                $this->jUnitFilePath,
                $this->projectDir,
                $this->infectionConfig->getSourceDirectories(),
                $skipCoverage,
                $this->infectionConfig->getExecuteOnlyCoveringTestCases(),
                $filteredSourceFilesToMutate
            );
        }

        if ($adapterName === TestFrameworkTypes::PEST) {
            $pestConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);

            return PestAdapterFactory::create(
                $this->testFrameworkFinder->find(
                    TestFrameworkTypes::PEST,
                    (string) $this->infectionConfig->getPhpUnit()->getCustomPath()
                ),
                $this->tmpDir,
                $pestConfigPath,
                (string) $this->infectionConfig->getPhpUnit()->getConfigDir(),
                $this->jUnitFilePath,
                $this->projectDir,
                $this->infectionConfig->getSourceDirectories(),
                $skipCoverage,
                $this->infectionConfig->getExecuteOnlyCoveringTestCases(),
                $filteredSourceFilesToMutate
            );
        }

        $availableTestFrameworks = [TestFrameworkTypes::PHPUNIT, TestFrameworkTypes::PEST];

        foreach ($this->installedExtensions as $installedExtension) {
            $factory = $installedExtension['extra']['class'];

            Assert::classExists($factory);

            if (!is_a($factory, TestFrameworkAdapterFactory::class, true)) {
                continue;
            }

            $availableTestFrameworks[] = $factory::getAdapterName();

            if ($adapterName === $factory::getAdapterName()) {
                return $factory::create(
                    $this->testFrameworkFinder->find($factory::getExecutableName()),
                    $this->tmpDir,
                    $this->configLocator->locate($factory::getAdapterName()),
                    null,
                    $this->jUnitFilePath,
                    $this->projectDir,
                    $this->infectionConfig->getSourceDirectories(),
                    $skipCoverage
                );
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid name of test framework "%s". Available names are: %s',
            $adapterName,
            implode(', ', $availableTestFrameworks)
        ));
    }

    /**
     * Get only those source files that will be mutated to use them in coverage whitelist
     *
     * @return list<string>
     */
    private function getFilteredSourceFilesToMutate(): array
    {
        if ($this->sourceFileFilter->getFilters() === []) {
            return [];
        }

        /** @var list<string> $filteredPaths */
        $filteredPaths = array_filter(array_map(
            static function (SplFileInfo $file) {
                return $file->getRealPath();
            },
            iterator_to_array($this->sourceFileFilter->filter($this->infectionConfig->getSourceFiles()))
        ));

        return $filteredPaths;
    }
}
