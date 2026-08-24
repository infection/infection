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

use function implode;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\FileSystem\Finder\StaticAnalysisToolExecutableFinder;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\ShellCommandLineExecutor;
use Infection\Source\Collector\SourceCollector;
use Infection\StaticAnalysis\Mago\Adapter\MagoAdapterFactory;
use Infection\StaticAnalysis\Mago\Mutant\MagoMutantExecutionResultFactory;
use Infection\StaticAnalysis\Mago\Process\MagoMutantProcessFactory;
use Infection\StaticAnalysis\PHPStan\Adapter\PHPStanAdapterFactory;
use Infection\StaticAnalysis\PHPStan\Mutant\PHPStanMutantExecutionResultFactory;
use Infection\StaticAnalysis\PHPStan\Process\PHPStanMutantProcessFactory;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\Contracts\StaticAnalysisTestFramework;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\TestFrameworkFactory;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapterFactory;
use InvalidArgumentException;
use function is_a;
use SplFileInfo;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class Factory
{
    /**
     * @param array<string, array<string, mixed>> $installedExtensions
     */
    public function __construct(
        private string $tmpDir,
        private string $projectDir,
        private TestFrameworkConfigLocatorInterface $configLocator,
        private TestFrameworkFinder $testFrameworkFinder,
        private StaticAnalysisToolExecutableFinder $staticAnalysisToolExecutableFinder,
        private TestFrameworkConfigLocatorInterface $staticAnalysisConfigLocator,
        private string $jUnitFilePath,
        private Configuration $infectionConfig,
        private SourceCollector $sourceCollector,
        private array $installedExtensions,
        private ShellCommandLineExecutor $shellCommandLineExecutor,
        private ConsoleOutput $consoleOutput,
        private CoverageChecker $coverageChecker,
        private InitialTestsRunner $initialTestsRunner,
        private MutantProcessContainerFactory $containerFactory,
        private TestFrameworkExtraOptionsFilter $extraOptionsFilter,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    public function create(string $adapterName, bool $skipCoverage): TestFramework
    {
        $testFramework = $this->createTestFramework($adapterName, $skipCoverage);

        return $testFramework instanceof TestFramework
            ? $testFramework
            : new LegacyTestFrameworkBridge(
                $testFramework,
                $this->consoleOutput,
                $this->coverageChecker,
                $this->initialTestsRunner,
                $this->infectionConfig,
                $this->containerFactory,
            );
    }

    public function createStaticAnalysisTool(string $adapterName, float $timeout): StaticAnalysisTestFramework
    {
        if ($adapterName === StaticAnalysisToolTypes::PHPSTAN) {
            $configPath = $this->staticAnalysisConfigLocator->locate($adapterName);
            $executable = $this->staticAnalysisToolExecutableFinder->find(
                $adapterName,
                (string) $this->infectionConfig->phpStan->customPath,
            );
            $options = $this->infectionConfig->getStaticAnalysisToolOptions();
            $commandLineBuilder = new CommandLineBuilder(new PhpExecutableFinder());

            return PHPStanAdapterFactory::create(
                $configPath,
                $executable,
                $options,
                $this->shellCommandLineExecutor,
                new InitialStaticAnalysisRunner($this->eventDispatcher),
                new PHPStanMutantProcessFactory(
                    new Filesystem(),
                    new PHPStanMutantExecutionResultFactory(),
                    $configPath,
                    $executable,
                    $commandLineBuilder,
                    $timeout,
                    $this->tmpDir,
                    $options,
                ),
            );
        }

        if ($adapterName === StaticAnalysisToolTypes::MAGO) {
            $configPath = $this->staticAnalysisConfigLocator->locate($adapterName);
            $executable = $this->staticAnalysisToolExecutableFinder->find(
                $adapterName,
                (string) $this->infectionConfig->mago->customPath,
            );
            $options = $this->infectionConfig->getStaticAnalysisToolOptions();
            $commandLineBuilder = new CommandLineBuilder(new PhpExecutableFinder());

            return MagoAdapterFactory::create(
                $configPath,
                $executable,
                $options,
                $this->shellCommandLineExecutor,
                new InitialStaticAnalysisRunner($this->eventDispatcher),
                new MagoMutantProcessFactory(
                    new MagoMutantExecutionResultFactory(),
                    $executable,
                    $commandLineBuilder,
                    $timeout,
                    $options,
                ),
            );
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid name of static analysis tool "%s". Available names are: %s',
            $adapterName,
            implode(', ', [StaticAnalysisToolTypes::PHPSTAN, StaticAnalysisToolTypes::MAGO]),
        ));
    }

    private function createTestFramework(string $adapterName, bool $skipCoverage): TestFramework|TestFrameworkAdapter
    {
        $availableTestFrameworks = [];

        foreach ($this->getTestFrameworkExtensions() as $installedExtension) {
            $factory = $installedExtension['extra']['class'];

            Assert::classExists($factory);

            if (!is_a($factory, TestFrameworkFactory::class, true)
                && !is_a($factory, TestFrameworkAdapterFactory::class, true)
            ) {
                continue;
            }

            $availableTestFrameworks[] = $factory::getAdapterName();

            if ($adapterName === $factory::getAdapterName()) {
                $configuration = $this->infectionConfig;
                $executable = $this->testFrameworkFinder->find(
                    $factory::getExecutableName(),
                    (string) ($installedExtension['extra']['customPath'] ?? ''),
                );
                $configDir = $installedExtension['extra']['configDir'] ?? null;

                if (is_a($factory, TestFrameworkFactory::class, true)) {
                    return $factory::create(
                        $executable,
                        $this->tmpDir,
                        $this->configLocator->locate($factory::getAdapterName()),
                        $configDir,
                        $this->jUnitFilePath,
                        $this->projectDir,
                        $configuration->source->directories,
                        $skipCoverage,
                        $configuration->executeOnlyCoveringTestCases,
                        $this->getFilteredSourceFilesToMutate(),
                        $configuration->mapSourceClassToTestStrategy,
                        $this->shellCommandLineExecutor,
                        $this->consoleOutput,
                        $this->coverageChecker,
                        $this->initialTestsRunner,
                        $configuration,
                        $this->containerFactory,
                        $this->extraOptionsFilter,
                    );
                }

                return $factory::create(
                    $executable,
                    $this->tmpDir,
                    $this->configLocator->locate($factory::getAdapterName()),
                    null,
                    $this->jUnitFilePath,
                    $this->projectDir,
                    $configuration->source->directories,
                    $skipCoverage,
                );
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid name of test framework "%s". Available names are: %s',
            $adapterName,
            implode(', ', $availableTestFrameworks),
        ));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getTestFrameworkExtensions(): array
    {
        return [
            'infection/phpunit-adapter' => [
                'extra' => [
                    'class' => PhpUnitAdapterFactory::class,
                    'configDir' => (string) $this->infectionConfig->phpUnit->configDir,
                    'customPath' => (string) $this->infectionConfig->phpUnit->customPath,
                ],
            ],
            ...$this->installedExtensions,
        ];
    }

    /**
     * Get only those source files that will be mutated. If the source is filtered by the user,
     * we do not need to execute the initial test run against all the sources, only the necessary
     * subset.
     *
     * @return SplFileInfo[]
     */
    private function getFilteredSourceFilesToMutate(): array
    {
        return $this->infectionConfig->sourceFilter === null
            ? []
            : $this->sourceCollector->collect();
    }
}
