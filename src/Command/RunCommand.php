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

use function file_exists;
use function implode;
use Infection\Configuration\Configuration;
use Infection\Configuration\Schema\SchemaConfigurationLoader;
use Infection\Console\ConsoleOutput;
use Infection\Console\Input\MsiParser;
use Infection\Console\IO;
use Infection\Console\LogVerbosity;
use Infection\Console\XdebugHandler;
use Infection\Container;
use Infection\Engine;
use Infection\Event\ApplicationExecutionWasStarted;
use Infection\FileSystem\Locator\FileNotFound;
use Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use Infection\FileSystem\Locator\Locator;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\TestFramework\TestFrameworkTypes;
use function Safe\sprintf;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use function trim;

/**
 * @internal
 */
final class RunCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('run')
            ->setDescription('Runs the mutation testing.')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Name of the Test framework to use ("%s")',
                    implode('", "', TestFrameworkTypes::TYPES)
                ),
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
                'Number of threads to use by the runner when executing the mutations',
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
                'Show escaped (and non-covered in verbose mode) mutations to the console'
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
                'Path to the configuration file to use'
            )
            ->addOption(
                'coverage',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to existing coverage directory'
            )
            ->addOption(
                'mutators',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify particular mutators, e.g. "--mutators=Plus,PublicVisibility"'
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
                '"dot" or "progress"',
                'dot'
            )
            ->addOption(
                'min-msi',
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Mutation Score Indicator (MSI) percentage value'
            )
            ->addOption(
                'min-covered-msi',
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Covered Code Mutation Score Indicator (MSI) percentage value'
            )
            ->addOption(
                'log-verbosity',
                null,
                InputOption::VALUE_REQUIRED,
                '"all" - full logs format, "default" - short logs format, "none" - no logs',
                LogVerbosity::NORMAL
            )
            ->addOption(
                'initial-tests-php-options',
                null,
                InputOption::VALUE_REQUIRED,
                'PHP options passed to the PHP executable when executing the initial tests. Will be ignored if "--coverage" option presented'
            )
            ->addOption(
                'skip-initial-tests',
                null,
                InputOption::VALUE_NONE,
                'Skips the initial test runs. Requires the coverage to be provided via the "--coverage" option'
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
                'Will not clean up Infection temporary folder'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Will not apply the mutations'
            )
        ;
    }

    protected function executeCommand(IO $io): void
    {
        $container = $this->createContainer($io->getInput());
        $consoleOutput = new ConsoleOutput($io);

        $this->startUp($container, $consoleOutput, $io);

        $engine = new Engine(
            $container->getConfiguration(),
            $container->getTestFrameworkAdapter(),
            $container->getCoverageChecker(),
            $container->getEventDispatcher(),
            $container->getInitialTestsRunner(),
            $container->getMemoryLimiter(),
            $container->getMutationGenerator(),
            $container->getMutationTestingRunner(),
            $container->getMinMsiChecker(),
            $consoleOutput,
            $container->getMetricsCalculator(),
            $container->getTestFrameworkExtraOptionsFilter()
        );

        try {
            $engine->execute();
        } catch (InitialTestsFailed | MinMsiCheckFailed $exception) {
            // TODO: we can move that in a dedicated logger later and handle those cases in the
            // Engine instead
            $io->error($exception->getMessage());
        }
    }

    private function createContainer(InputInterface $input): Container
    {
        // Currently the configuration is mandatory hence there is no way to
        // say "do not use a config". If this becomes possible in the future
        // though, it will likely be a `--no-config` option rather than relying
        // on this value to be set to an empty string.
        $configFile = trim((string) $input->getOption('configuration'));

        $coverage = trim((string) $input->getOption('coverage'));
        $testFramework = trim((string) $input->getOption('test-framework'));
        $testFrameworkExtraOptions = trim((string) $input->getOption('test-framework-options'));
        $initialTestsPhpOptions = trim((string) $input->getOption('initial-tests-php-options'));

        /** @var string|null $minMsi */
        $minMsi = $input->getOption('min-msi');
        /** @var string|null $minCoveredMsi */
        $minCoveredMsi = $input->getOption('min-covered-msi');

        $msiPrecision = MsiParser::detectPrecision($minMsi, $minCoveredMsi);

        return $this->getApplication()->getContainer()->withDynamicParameters(
            $configFile === '' ? null : $configFile,
            trim((string) $input->getOption('mutators')),
            $input->getOption('show-mutations'),
            trim((string) $input->getOption('log-verbosity')),
            $input->getOption('debug'),
            $input->getOption('only-covered'),
            trim((string) $input->getOption('formatter')),
            $input->getOption('no-progress'),
            $coverage === '' ? null : $coverage,
            $initialTestsPhpOptions === '' ? null : $initialTestsPhpOptions,
            (bool) $input->getOption('skip-initial-tests'),
            $input->getOption('ignore-msi-with-no-mutations'),
            MsiParser::parse($minMsi, $msiPrecision, 'min-msi'),
            MsiParser::parse($minCoveredMsi, $msiPrecision, 'min-covered-msi'),
            $msiPrecision,
            $testFramework === '' ? null : $testFramework,
            $testFrameworkExtraOptions === '' ? null : $testFrameworkExtraOptions,
            trim((string) $input->getOption('filter')),
            (int) $input->getOption('threads'),
            (bool) $input->getOption('dry-run')
        );
    }

    private function installTestFrameworkIfNeeded(Container $container, IO $io): void
    {
        $installationDecider = $container->getAdapterInstallationDecider();
        $configTestFramework = $container->getConfiguration()->getTestFramework();

        $adapterName = trim((string) $input->getOption('test-framework')) ?: $configTestFramework;

        if (!$installationDecider->shouldBeInstalled($adapterName, $io)) {
            return;
        }

        $io->newLine();
        $io->writeln(sprintf(
            'Installing <comment>infection/%s-adapter</comment>...',
            $adapterName
        ));

        $container->getAdapterInstaller()->install($adapterName);
    }

    private function startUp(Container $container, ConsoleOutput $consoleOutput, IO $io): void
    {
        $locator = $container->getRootsFileOrDirectoryLocator();

        if ($customConfigPath = (string) $input->getOption('configuration')) {
            $locator->locate($customConfigPath);
        } else {
            $this->runConfigurationCommand($locator, $io);
        }

        $this->installTestFrameworkIfNeeded($container, $io);

        // Check if the application needs a restart _after_ configuring the command or adding
        // a missing test framework
        XdebugHandler::check(new ConsoleLogger($io->getOutput()));

        $application = $this->getApplication();

        $io->writeln($application->getHelp());
        $io->newLine();

        $this->logRunningWithDebugger($consoleOutput);

        if (!$application->isAutoExitEnabled()) {
            // When we're not in control of exit codes, that means it's the caller
            // responsibility to disable xdebug if it isn't needed. As of writing
            // that's only the case during E2E testing. Show a warning nevertheless.

            $consoleOutput->logNotInControlOfExitCodes();
        }

        $container->getCoverageChecker()->checkCoverageRequirements();

        $config = $container->getConfiguration();

        $this->includeUserBootstrap($config);

        $container->getFileSystem()->mkdir($config->getTmpDir());

        LogVerbosity::convertVerbosityLevel($io->getInput(), $consoleOutput);

        $container->getSubscriberRegisterer()->registerSubscribers($io->getOutput());

        $container->getEventDispatcher()->dispatch(new ApplicationExecutionWasStarted());
    }

    private function runConfigurationCommand(Locator $locator, IO $io): void
    {
        try {
            $locator->locateOneOf([
                SchemaConfigurationLoader::DEFAULT_CONFIG_FILE,
                SchemaConfigurationLoader::DEFAULT_DIST_CONFIG_FILE,
            ]);
        } catch (FileNotFound | FileOrDirectoryNotFound $exception) {
            $configureCommand = $this->getApplication()->find('configure');

            $args = [
                '--test-framework' => $io->getInput()->getOption('test-framework') ?: TestFrameworkTypes::PHPUNIT,
            ];

            $newInput = new ArrayInput($args);
            $newInput->setInteractive($io->isInteractive());
            $configureCommand->run($newInput, $io->getOutput());
        }
    }

    private function includeUserBootstrap(Configuration $config): void
    {
        $bootstrap = $config->getBootstrap();

        if ($bootstrap === null) {
            return;
        }

        if (!file_exists($bootstrap)) {
            throw FileOrDirectoryNotFound::fromFileName($bootstrap, [__DIR__]);
        }

        (static function (string $infectionBootstrapFile): void {
            require_once $infectionBootstrapFile;
        })($bootstrap);
    }

    private function logRunningWithDebugger(ConsoleOutput $consoleOutput): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $consoleOutput->logRunningWithDebugger(PHP_SAPI);
        } elseif (extension_loaded('xdebug')) {
            $consoleOutput->logRunningWithDebugger('Xdebug');
        } elseif (extension_loaded('pcov')) {
            $consoleOutput->logRunningWithDebugger('PCOV');
        }
    }
}
