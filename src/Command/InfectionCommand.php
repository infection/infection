<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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
use Infection\Console\ConsoleOutput;
use Infection\Console\Exception\ConfigurationException;
use Infection\Console\Exception\InfectionException;
use Infection\Console\LogVerbosity;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\ApplicationExecutionFinished;
use Infection\Events\ApplicationExecutionStarted;
use Infection\Finder\Exception\LocatorException;
use Infection\Finder\Locator;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\HasExtraNodeVisitors;
use Infection\TestFramework\PhpSpec\PhpSpecExtraOptions;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\PhpUnit\PhpUnitExtraOptions;
use Infection\TestFramework\TestFrameworkExtraOptions;
use Infection\TestFramework\TestFrameworkTypes;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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

    protected function configure(): void
    {
        $this->setName('run')
            ->setDescription('Runs the mutation testing.')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the Test framework to use (phpunit, phpspec)',
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
                'Path to existing coverage (`xml` and `junit` reports are required)',
                ''
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
                'Extra php options for the initial test runner. Will be ignored if --coverage option presented.',
                ''
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
        $container = $this->getContainer();

        if (!$container->get('coverage.checker')->hasDebuggerOrCoverageOption()) {
            $this->consoleOutput->logMissedDebuggerOrCoverageOption();

            return 1;
        }

        $config = $container->get('infection.config');

        $this->includeUserBootstrap($config);

        $testFrameworkKey = $input->getOption('test-framework') ?: $config->getTestFramework();
        $adapter = $container->get('test.framework.factory')->create($testFrameworkKey, $this->skipCoverage);

        LogVerbosity::convertVerbosityLevel($input, $this->consoleOutput);

        $metricsCalculator = $container->get('metrics');
        $container->get('subscriber.builder')->registerSubscribers($adapter, $output);

        $this->eventDispatcher->dispatch(new ApplicationExecutionStarted());

        $processBuilder = new ProcessBuilder($adapter, $config->getProcessTimeout());
        $testFrameworkOptions = $this->getTestFrameworkExtraOptions($testFrameworkKey);

        $initialTestsRunner = new InitialTestsRunner($processBuilder, $this->eventDispatcher);
        $initialTestSuitProcess = $initialTestsRunner->run(
            $testFrameworkOptions->getForInitialProcess(),
            $this->skipCoverage,
            explode(
                ' ',
                $input->hasOption('initial-tests-php-options') ? $input->getOption('initial-tests-php-options') :
                    $config->getInitialTestsPhpOptions()
            )
        );

        if (!$initialTestSuitProcess->isSuccessful()) {
            $this->consoleOutput->logInitialTestsDoNotPass($initialTestSuitProcess, $adapter);

            return 1;
        }

        $this->assertCodeCoverageExists($initialTestSuitProcess, $testFrameworkKey);

        $container->get('memory.limit.applier')->applyMemoryLimitFromProcess($initialTestSuitProcess, $adapter);

        $codeCoverageData = $this->getCodeCoverageData($testFrameworkKey);
        $mutationsGenerator = new MutationsGenerator(
            $container->get('src.dirs'),
            $container->get('exclude.paths'),
            $codeCoverageData,
            $container->get('mutators'),
            $this->eventDispatcher,
            $container->get('parser')
        );

        $mutations = $mutationsGenerator->generate(
            $input->getOption('only-covered'),
            $input->getOption('filter'),
            $adapter instanceof HasExtraNodeVisitors ? $adapter->getMutationsCollectionNodeVisitors() : []
        );

        $mutationTestingRunner = new MutationTestingRunner(
            $processBuilder,
            $container->get('parallel.process.runner'),
            $container->get('mutant.creator'),
            $this->eventDispatcher,
            $mutations
        );

        $mutationTestingRunner->run(
            (int) $this->input->getOption('threads'),
            $codeCoverageData,
            $testFrameworkOptions->getForMutantProcess()
        );
        /** @var TestRunConstraintChecker $constraintChecker */
        $constraintChecker = $container->get('test.run.constraint.checker');

        $statusCode = 0;

        if (!$constraintChecker->hasTestRunPassedConstraints()) {
            $this->consoleOutput->logBadMsiErrorMessage(
                $metricsCalculator,
                $constraintChecker->getMinRequiredValue(),
                $constraintChecker->getErrorType()
            );

            $statusCode = 1;
        }

        $this->eventDispatcher->dispatch(new ApplicationExecutionFinished());

        return $statusCode;
    }

    /**
     * Run configuration command if config does not exist
     *
     *
     * @throws InfectionException
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $locator = $this->getContainer()->get('locator');

        if ($customConfigPath = $input->getOption('configuration')) {
            $locator->locate($customConfigPath);
        } else {
            $this->runConfigurationCommand($locator);
        }

        $this->consoleOutput = $this->getApplication()->getConsoleOutput();
        $this->skipCoverage = \strlen(trim($input->getOption('coverage'))) > 0;
        $this->eventDispatcher = $this->getContainer()->get('dispatcher');
    }

    private function includeUserBootstrap(InfectionConfig $config): void
    {
        $bootstrap = $config->getBootstrap();

        if ($bootstrap) {
            if (!file_exists($bootstrap)) {
                throw LocatorException::fileOrDirectoryDoesNotExist($bootstrap);
            }

            (function ($infectionBootstrapFile): void {
                require_once $infectionBootstrapFile;
            })($bootstrap);
        }
    }

    private function getCodeCoverageData(string $testFrameworkKey): CodeCoverageData
    {
        $coverageDir = $this->getContainer()->get(sprintf('coverage.dir.%s', $testFrameworkKey));
        $testFileDataProviderServiceId = sprintf('test.file.data.provider.%s', $testFrameworkKey);
        $testFileDataProviderService = $this->getContainer()->has($testFileDataProviderServiceId)
            ? $this->getContainer()->get($testFileDataProviderServiceId)
            : null;

        return new CodeCoverageData($coverageDir, new CoverageXmlParser($coverageDir), $testFrameworkKey, $testFileDataProviderService);
    }

    private function getTestFrameworkExtraOptions(string $testFrameworkKey): TestFrameworkExtraOptions
    {
        $extraOptions = $this->input->hasOption('test-framework-options')
            ? $this->input->getOption('test-framework-options') : $this->getContainer()->get('infection.config')->getTestFrameworkOptions();

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
            $config = $this->getContainer()->get('infection.config');

            $args = [
                '--test-framework' => $this->input->getOption('test-framework') ?: $config->getTestFramework(),
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
        $coverageDir = $this->getContainer()->get(sprintf('coverage.dir.%s', $testFrameworkKey));

        $coverageIndexFilePath = $coverageDir . '/' . CodeCoverageData::COVERAGE_INDEX_FILE_NAME;

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
