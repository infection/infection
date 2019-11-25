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

namespace Infection\Command;

use Infection\Config\InfectionConfig;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Console\Exception\ConfigurationException;
use Infection\Console\Exception\InfectionException;
use Infection\Console\InfectionContainer;
use Infection\Console\LogVerbosity;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\ApplicationExecutionFinished;
use Infection\Events\ApplicationExecutionStarted;
use Infection\Locator\FileOrDirectoryNotFound;
use Infection\Locator\Locator;
use Infection\Locator\RootsFileOrDirectoryLocator;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Process\Builder\InitialTestRunProcessBuilder;
use Infection\Process\Builder\MutantProcessBuilder;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\TestFramework\Coverage\XMLLineCodeCoverage;
use Infection\TestFramework\HasExtraNodeVisitors;
use Infection\TestFramework\PhpSpec\PhpSpecExtraOptions;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\PhpUnit\PhpUnitExtraOptions;
use Infection\TestFramework\TestFrameworkAdapter;
use Infection\TestFramework\TestFrameworkExtraOptions;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Utils\VersionParser;
use function is_numeric;
use function Safe\sprintf;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class InfectionCommand extends BaseCommand
{
    /**
     * @var ConsoleOutput
     */
    private $consoleOutput;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $skipCoverage;

    /**
     * @var InfectionContainer
     */
    private $container;

    /**
     * @var string
     */
    private $testFrameworkKey = '';

    /**
     * @var TestFrameworkExtraOptions
     */
    private $testFrameworkOptions;

    /**
     * @var VersionParser
     */
    private $versionParser;

    protected function configure(): void
    {
        $this->setName('run')
            ->setDescription('Runs the mutation testing.')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the Test framework to use (' . implode(', ', TestFrameworkTypes::TYPES) . ')',
                ''
            )
            ->addOption(
                'test-framework-options',
                null,
                InputOption::VALUE_REQUIRED,
                'Options to be passed to the test framework'
            )
            ->addOption(
                'threads',
                'j',
                InputOption::VALUE_REQUIRED,
                'Threads count',
                '1'
            )
            ->addOption(
                'only-covered',
                null,
                InputOption::VALUE_NONE,
                'Mutate only covered by tests lines of code'
            )
            ->addOption(
                'show-mutations',
                's',
                InputOption::VALUE_NONE,
                'Show mutations to the console'
            )
            ->addOption(
                'no-progress',
                null,
                InputOption::VALUE_NONE,
                'Do not output progress bars'
            )
            ->addOption(
                'configuration',
                'c',
                InputOption::VALUE_REQUIRED,
                'Custom configuration file path'
            )
            ->addOption(
                'coverage',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to existing coverage (`xml` and `junit` reports are required)'
            )
            ->addOption(
                'mutators',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify particular mutators. Example: --mutators=Plus,PublicVisibility'
            )
            ->addOption(
                'filter',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter which files to mutate',
                ''
            )
            ->addOption(
                'formatter',
                null,
                InputOption::VALUE_REQUIRED,
                'Output formatter. Possible values: dot, progress',
                'dot'
            )
            ->addOption(
                'min-msi',
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Mutation Score Indicator (MSI) percentage value. Should be used in CI server.'
            )
            ->addOption(
                'min-covered-msi',
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Covered Code Mutation Score Indicator (MSI) percentage value. Should be used in CI server.'
            )
            ->addOption(
                'log-verbosity',
                null,
                InputOption::VALUE_REQUIRED,
                'Log verbosity level. \'all\' - full logs format, \'default\' - short logs format, \'none\' - no logs.',
                LogVerbosity::NORMAL
            )
            ->addOption(
                'initial-tests-php-options',
                null,
                InputOption::VALUE_REQUIRED,
                'Extra php options for the initial test runner. Will be ignored if --coverage option presented.'
            )
            ->addOption(
                'ignore-msi-with-no-mutations',
                null,
                InputOption::VALUE_NONE,
                'Ignore MSI violations with zero mutations'
            )
            ->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE,
                'Debug mode. Will not clean up Infection temporary folder.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = $this->startUp();
        $this->runInitialTestSuite($adapter);
        $this->runMutationTesting($adapter);

        if (!$this->checkMetrics()) {
            return 1;
        }

        return 0;
    }

    /**
     * Run configuration command if config does not exist
     *
     * @throws InfectionException
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->initContainer($input);

        $locator = $this->container[RootsFileOrDirectoryLocator::class];

        if ($customConfigPath = $input->getOption('configuration')) {
            $locator->locate($customConfigPath);
        } else {
            $this->runConfigurationCommand($locator);
        }

        $this->consoleOutput = $this->getApplication()->getConsoleOutput();
        $this->skipCoverage = trim((string) $input->getOption('coverage')) !== '';
        $this->eventDispatcher = $this->container['dispatcher'];
        $this->versionParser = $this->container[VersionParser::class];
    }

    private function startUp(): TestFrameworkAdapter
    {
        Assert::notNull($this->container);

        if (!$this->container['coverage.checker']->hasDebuggerOrCoverageOption()) {
            throw CoverageDoesNotExistException::unableToGenerate();
        }

        /** @var Configuration $config */
        $config = $this->container[Configuration::class];

        $this->includeUserBootstrap($config);

        $this->testFrameworkKey = $config->getTestFramework() ?? TestFrameworkTypes::PHPUNIT;
        $this->testFrameworkOptions = $this->getTestFrameworkExtraOptions($this->testFrameworkKey);
        $adapter = $this->container['test.framework.factory']->create($this->testFrameworkKey, $this->skipCoverage);

        LogVerbosity::convertVerbosityLevel($this->input, $this->consoleOutput);

        $this->container['subscriber.builder']->registerSubscribers($adapter, $this->output);

        $this->eventDispatcher->dispatch(new ApplicationExecutionStarted());

        return $adapter;
    }

    private function runInitialTestSuite(TestFrameworkAdapter $adapter): void
    {
        /** @var Configuration $config */
        $config = $this->container[Configuration::class];

        $processBuilder = new InitialTestRunProcessBuilder($adapter, $this->versionParser);

        $initialTestsRunner = new InitialTestsRunner($processBuilder, $this->eventDispatcher);

        $initialTestSuitProcess = $initialTestsRunner->run(
            $this->testFrameworkOptions->getForInitialProcess(),
            $this->skipCoverage,
            explode(' ', (string) $config->getInitialTestsPhpOptions())
        );

        if (!$initialTestSuitProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcessAndAdapter($initialTestSuitProcess, $adapter);
        }

        $this->assertCodeCoverageExists($initialTestSuitProcess, $this->testFrameworkKey);

        $this->container['memory.limit.applier']->applyMemoryLimitFromProcess($initialTestSuitProcess, $adapter);
    }

    private function runMutationTesting(TestFrameworkAdapter $adapter): void
    {
        /** @var Configuration $config */
        $config = $this->container[Configuration::class];

        $processBuilder = new MutantProcessBuilder($adapter, $this->versionParser, $config->getProcessTimeout());

        $codeCoverageData = $this->getCodeCoverageData($this->testFrameworkKey);

        $mutationsGenerator = new MutationsGenerator(
            $config->getSource()->getDirectories(),
            $config->getSource()->getExcludes(),
            $codeCoverageData,
            $config->getMutators(),
            $this->eventDispatcher,
            $this->container['parser']
        );

        $mutations = $mutationsGenerator->generate(
            $this->input->getOption('only-covered'),
            $this->input->getOption('filter'),
            $adapter instanceof HasExtraNodeVisitors ? $adapter->getMutationsCollectionNodeVisitors() : []
        );

        $mutationTestingRunner = new MutationTestingRunner(
            $processBuilder,
            $this->container['parallel.process.runner'],
            $this->container['mutant.creator'],
            $this->eventDispatcher,
            $mutations
        );

        $mutationTestingRunner->run(
            (int) $this->input->getOption('threads'),
            $this->testFrameworkOptions->getForMutantProcess()
        );
    }

    private function checkMetrics(): bool
    {
        /** @var TestRunConstraintChecker $constraintChecker */
        $constraintChecker = $this->container['test.run.constraint.checker'];

        if (!$constraintChecker->hasTestRunPassedConstraints()) {
            $this->consoleOutput->logBadMsiErrorMessage(
                $this->container['metrics'],
                $constraintChecker->getMinRequiredValue(),
                $constraintChecker->getErrorType()
            );

            return false;
        }

        foreach ([TestRunConstraintChecker::MSI_FAILURE, TestRunConstraintChecker::COVERED_MSI_FAILURE] as $type) {
            if ($constraintChecker->isMsiOverMinimumMsi($type)) {
                $this->consoleOutput->logMinMsiCanGetIncreasedNotice(
                    $this->container['metrics'],
                    $constraintChecker->getMinRequiredValue(),
                    $type
                );
            }
        }

        $this->eventDispatcher->dispatch(new ApplicationExecutionFinished());

        return true;
    }

    private function initContainer(InputInterface $input): void
    {
        // Currently the configuration is mandatory hence there is no way to
        // say "do not use a config". If this becomes possible in the future
        // though, it will likely be a `--no-config` option rather than relying
        // on this value to be set to an empty string.
        $configFile = trim((string) $input->getOption('configuration'));

        $coverage = trim((string) $input->getOption('coverage'));
        $testFramework = trim((string) $this->input->getOption('test-framework'));
        $testFrameworkOptions = trim((string) $this->input->getOption('test-framework-options'));
        $initialTestsPhpOptions = trim((string) $input->getOption('initial-tests-php-options'));

        $minMsi = $input->getOption('min-msi');

        if (null !== $minMsi && !is_numeric($minMsi)) {
            throw new InvalidArgumentException(sprintf('Expected min-msi to be a float. Got "%s"', $minMsi));
        }

        $minCoveredMsi = $input->getOption('min-covered-msi');

        if (null !== $minCoveredMsi && !is_numeric($minCoveredMsi)) {
            throw new InvalidArgumentException(sprintf('Expected min-covered-msi to be a float. Got "%s"', $minCoveredMsi));
        }

        $mutators = trim((string) $input->getOption('mutators'));

        $this->container = $this->getApplication()->getContainer()->withDynamicParameters(
            '' === $configFile ? null : $configFile,
            '' === $mutators ? null : $mutators,
            $input->getOption('show-mutations'),
            trim((string) $input->getOption('log-verbosity')),
            $input->getOption('debug'),
            $input->getOption('only-covered'),
            trim((string) $input->getOption('formatter')),
            $input->getOption('no-progress'),
            '' === $coverage ? null : $coverage,
            '' === $initialTestsPhpOptions ? null : $initialTestsPhpOptions,
            $input->getOption('ignore-msi-with-no-mutations'),
            null === $minMsi ? null : (float) $minMsi,
            null === $minCoveredMsi ? null : (float) $minCoveredMsi,
            '' === $testFramework ? null : $testFramework,
            '' === $testFrameworkOptions ? null : $testFrameworkOptions
        );
    }

    private function includeUserBootstrap(Configuration $config): void
    {
        $bootstrap = $config->getBootstrap();

        if (null === $bootstrap) {
            return;
        }

        if (!file_exists($bootstrap)) {
            throw FileOrDirectoryNotFound::fromFileName($bootstrap, [__DIR__]);
        }

        (static function (string $infectionBootstrapFile): void {
            require_once $infectionBootstrapFile;
        })($bootstrap);
    }

    private function getCodeCoverageData(string $testFrameworkKey): LineCodeCoverage
    {
        $coverageDir = $this->container[sprintf('coverage.dir.%s', $testFrameworkKey)];
        $testFileDataProviderServiceId = sprintf('test.file.data.provider.%s', $testFrameworkKey);
        $testFileDataProviderService = $this->container->offsetExists($testFileDataProviderServiceId)
            ? $this->container[$testFileDataProviderServiceId]
            : null;

        return new XMLLineCodeCoverage($coverageDir, new CoverageXmlParser($coverageDir), $testFrameworkKey, $testFileDataProviderService);
    }

    private function getTestFrameworkExtraOptions(string $testFrameworkKey): TestFrameworkExtraOptions
    {
        /** @var Configuration $config */
        $config = $this->container[Configuration::class];

        $extraOptions = $config->getTestFrameworkOptions();

        return TestFrameworkTypes::PHPUNIT === $testFrameworkKey
            ? new PhpUnitExtraOptions($extraOptions)
            : new PhpSpecExtraOptions($extraOptions);
    }

    private function runConfigurationCommand(Locator $locator): void
    {
        try {
            $locator->locateOneOf(InfectionConfig::POSSIBLE_CONFIG_FILE_NAMES);
        } catch (\Exception $e) {
            $configureCommand = $this->getApplication()->find('configure');

            $args = [
                '--test-framework' => $this->input->getOption('test-framework') ?: TestFrameworkTypes::PHPUNIT,
            ];

            $newInput = new ArrayInput($args);
            $newInput->setInteractive($this->input->isInteractive());
            $result = $configureCommand->run($newInput, $this->output);

            if ($result !== 0) {
                throw ConfigurationException::configurationAborted();
            }
        }
    }

    private function assertCodeCoverageExists(Process $initialTestsProcess, string $testFrameworkKey): void
    {
        $coverageDir = $this->container[sprintf('coverage.dir.%s', $testFrameworkKey)];

        $coverageIndexFilePath = $coverageDir . '/' . XMLLineCodeCoverage::COVERAGE_INDEX_FILE_NAME;

        $processInfo = sprintf(
            '%sCommand line: %s%sProcess Output: %s',
            PHP_EOL,
            $initialTestsProcess->getCommandLine(),
            PHP_EOL,
            $initialTestsProcess->getOutput()
        );

        if (!file_exists($coverageIndexFilePath)) {
            throw CoverageDoesNotExistException::with(
                $coverageIndexFilePath,
                $testFrameworkKey,
                \dirname($coverageIndexFilePath, 2),
                $processInfo
            );
        }
    }
}
