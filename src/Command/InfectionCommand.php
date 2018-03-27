<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Command;

use Infection\Config\InfectionConfig;
use Infection\Console\Exception\InfectionException;
use Infection\Console\Exception\InvalidOptionException;
use Infection\Console\LogVerbosity;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Finder\Exception\LocatorException;
use Infection\Mutant\Exception\MsiCalculationException;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\Process\Listener\MutantCreatingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationGeneratingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationTestingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationTestingResultsLoggerSubscriber;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\MemoryUsageAware;
use Infection\TestFramework\PhpSpec\PhpSpecExtraOptions;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\PhpUnit\PhpUnitExtraOptions;
use Infection\TestFramework\TestFrameworkExtraOptions;
use Infection\TestFramework\TestFrameworkTypes;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class InfectionCommand extends BaseCommand
{
    const CI_FLAG_ERROR = 'The minimum required %s percentage should be %s%%, but actual is %s%%. Improve your tests!';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $skipCoverage;

    protected function configure()
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
                1
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
                InputOption::VALUE_OPTIONAL,
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $config = $container->get('infection.config');

        $bootstrap = $config->getBootstrap();
        if ($bootstrap) {
            if (!file_exists($bootstrap)) {
                throw LocatorException::fileOrDirectoryDoesNotExist($bootstrap);
            }

            require_once $bootstrap;
        }

        $testFrameworkKey = $input->getOption('test-framework') ?: $config->getTestFramework();
        $adapter = $container->get('test.framework.factory')->create($testFrameworkKey, $this->skipCoverage);

        $this->checkLoggingLevels($input->getOption('log-verbosity'));

        $metricsCalculator = new MetricsCalculator();
        $this->registerSubscribers($metricsCalculator, $adapter);

        $processBuilder = new ProcessBuilder($adapter, $config->getProcessTimeout());
        $testFrameworkOptions = $this->getTestFrameworkExtraOptions($testFrameworkKey);

        $initialTestsRunner = new InitialTestsRunner($processBuilder, $this->eventDispatcher);
        $initialTestSuitProcess = $initialTestsRunner->run(
            $testFrameworkOptions->getForInitialProcess(),
            $this->skipCoverage,
            explode(' ', $input->getOption('initial-tests-php-options'))
        );

        if (!$initialTestSuitProcess->isSuccessful()) {
            $this->logInitialTestsDoNotPass($initialTestSuitProcess, $testFrameworkKey);

            return 1;
        }

        // We only apply a memory limit if there isn't one set
        if ($adapter instanceof MemoryUsageAware && ini_get('memory_limit') === '-1') {
            $this->applyMemoryLimitFromPhpUnitProcess($initialTestSuitProcess, $adapter);
        }

        $codeCoverageData = $this->getCodeCoverageData($testFrameworkKey);
        $mutationsGenerator = new MutationsGenerator(
            $container->get('src.dirs'),
            $container->get('exclude.paths'),
            $codeCoverageData,
            $container->get('mutators'),
            $this->parseMutators($input->getOption('mutators')),
            $this->eventDispatcher,
            $container->get('parser')
        );

        $mutations = $mutationsGenerator->generate($input->getOption('only-covered'), $input->getOption('filter'));

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

        $ignoreMsi = (bool) $input->getOption('ignore-msi-with-no-mutations');

        if ($ignoreMsi && \count($mutations) === 0) {
            return 0;
        }

        if ($this->hasBadMsi($metricsCalculator)) {
            $this->io->error($this->getBadMsiErrorMessage($metricsCalculator));

            return 1;
        }

        if ($this->hasBadCoveredMsi($metricsCalculator)) {
            $this->io->error($this->getBadCoveredMsiErrorMessage($metricsCalculator));

            return 1;
        }

        return 0;
    }

    private function getOutputFormatter(): OutputFormatter
    {
        if ($this->input->getOption('formatter') === 'progress') {
            return new ProgressFormatter(new ProgressBar($this->output));
        }

        if ($this->input->getOption('formatter') === 'dot') {
            return new DotFormatter($this->output);
        }

        throw new \InvalidArgumentException('Incorrect formatter. Possible values: "dot", "progress"');
    }

    private function applyMemoryLimitFromPhpUnitProcess(Process $process, PhpUnitAdapter $adapter)
    {
        $tempConfigPath = \php_ini_loaded_file();

        if (empty($tempConfigPath) || !file_exists($tempConfigPath) || !is_writable($tempConfigPath)) {
            // Cannot add a memory limit: there is no php.ini file or it is not writeable
            return;
        }

        $memoryLimit = $adapter->getMemoryUsed($process->getOutput());

        if ($memoryLimit < 0) {
            // Cannot detect memory used, not setting any limits
            return;
        }

        /*
         * Since we know how much memory the initial test suite used,
         * and only if we know, we can enforce a memory limit upon all
         * mutation processes. Limit is set to be twice the known amount,
         * because if we know that a normal test suite used X megabytes,
         * if a mutants uses a lot more, this is a definite error.
         *
         * By default we let a mutant process use twice as much more
         * memory as an initial test suite consumed.
         */
        $memoryLimit *= 2;

        file_put_contents($tempConfigPath, PHP_EOL . sprintf('memory_limit = %dM', $memoryLimit), FILE_APPEND);
    }

    private function registerSubscribers(
        MetricsCalculator $metricsCalculator,
        AbstractTestFrameworkAdapter $testFrameworkAdapter
    ) {
        foreach ($this->getSubscribers($metricsCalculator, $testFrameworkAdapter) as $subscriber) {
            $this->eventDispatcher->addSubscriber($subscriber);
        }
    }

    private function getSubscribers(
        MetricsCalculator $metricsCalculator,
        AbstractTestFrameworkAdapter $testFrameworkAdapter
    ): array {
        $initialTestsProgressBar = new ProgressBar($this->output);
        $initialTestsProgressBar->setFormat('verbose');

        $mutationGeneratingProgressBar = new ProgressBar($this->output);
        $mutationGeneratingProgressBar->setFormat('Processing source code files: %current%/%max%');

        $mutantCreatingProgressBar = new ProgressBar($this->output);
        $mutantCreatingProgressBar->setFormat('Creating mutated files and processes: %current%/%max%');

        return [
            new InitialTestsConsoleLoggerSubscriber(
                $this->output,
                $initialTestsProgressBar,
                $testFrameworkAdapter
            ),
            new MutationGeneratingConsoleLoggerSubscriber(
                $this->output,
                $mutationGeneratingProgressBar
            ),
            new MutantCreatingConsoleLoggerSubscriber(
                $this->output,
                $mutantCreatingProgressBar
            ),
            new MutationTestingConsoleLoggerSubscriber(
                $this->output,
                $this->getOutputFormatter(),
                $metricsCalculator,
                $this->getContainer()->get('diff.colorizer'),
                $this->input->getOption('show-mutations')
            ),
            new MutationTestingResultsLoggerSubscriber(
                $this->output,
                $this->getContainer()->get('infection.config'),
                $metricsCalculator,
                $this->getContainer()->get('filesystem'),
                $this->input->getOption('log-verbosity')
            ),
        ];
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

    private function logInitialTestsDoNotPass(Process $initialTestSuitProcess, string $testFrameworkKey)
    {
        $lines = [
            'Project tests must be in a passing state before running Infection.',
            sprintf(
                '%s reported an exit code of %d.',
                ucfirst($testFrameworkKey),
                $initialTestSuitProcess->getExitCode()
            ),
            sprintf(
                'Refer to the %s\'s output below:',
                $testFrameworkKey
            ),
        ];

        if ($stdOut = $initialTestSuitProcess->getOutput()) {
            $lines[] = 'STDOUT:';
            $lines[] = $stdOut;
        }

        if ($stdError = $initialTestSuitProcess->getErrorOutput()) {
            $lines[] = 'STDERR:';
            $lines[] = $stdError;
        }

        $this->io->error($lines);
    }

    private function hasBadMsi(MetricsCalculator $metricsCalculator): bool
    {
        $minMsi = (float) $this->input->getOption('min-msi');

        return $minMsi && ($metricsCalculator->getMutationScoreIndicator() < $minMsi);
    }

    private function hasBadCoveredMsi(MetricsCalculator $metricsCalculator): bool
    {
        $minCoveredMsi = (float) $this->input->getOption('min-covered-msi');

        return $minCoveredMsi && ($metricsCalculator->getCoveredCodeMutationScoreIndicator() < $minCoveredMsi);
    }

    private function getBadMsiErrorMessage(MetricsCalculator $metricsCalculator): string
    {
        if ($minMsi = (float) $this->input->getOption('min-msi')) {
            return sprintf(
                self::CI_FLAG_ERROR,
                'MSI',
                $minMsi,
                $metricsCalculator->getMutationScoreIndicator()
            );
        }

        throw MsiCalculationException::create('min-msi');
    }

    private function getBadCoveredMsiErrorMessage(MetricsCalculator $metricsCalculator): string
    {
        if ($minCoveredMsi = (float) $this->input->getOption('min-covered-msi')) {
            return sprintf(
                self::CI_FLAG_ERROR,
                'Covered Code MSI',
                $minCoveredMsi,
                $metricsCalculator->getCoveredCodeMutationScoreIndicator()
            );
        }

        throw MsiCalculationException::create('min-covered-msi');
    }

    private function parseMutators(string $mutators = null): array
    {
        if ($mutators === null) {
            return [];
        }

        $trimmedMutators = trim($mutators);

        if ($trimmedMutators === '') {
            throw InvalidOptionException::withMessage('The "--mutators" option requires a value.');
        }

        return explode(',', $mutators);
    }

    private function getTestFrameworkExtraOptions(string $testFrameworkKey): TestFrameworkExtraOptions
    {
        $extraOptions = $this->input->getOption('test-framework-options');

        return TestFrameworkTypes::PHPUNIT === $testFrameworkKey
            ? new PhpUnitExtraOptions($extraOptions)
            : new PhpSpecExtraOptions($extraOptions);
    }

    /**
     * Run configuration command if config does not exist
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws InfectionException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $customConfigPath = $input->getOption('configuration');
        $configExists = $customConfigPath && file_exists($customConfigPath);

        if (!$configExists) {
            $configExists = file_exists(InfectionConfig::CONFIG_FILE_NAME)
                || file_exists(InfectionConfig::CONFIG_FILE_NAME . '.dist');
        }

        if (!$configExists) {
            $configureCommand = $this->getApplication()->find('configure');

            $args = [
                '--test-framework' => $input->getOption('test-framework'),
            ];

            $newInput = new ArrayInput($args);
            $newInput->setInteractive($input->isInteractive());
            $result = $configureCommand->run($newInput, $output);

            if ($result !== 0) {
                throw InfectionException::configurationAborted();
            }
        }

        $this->io = $this->getApplication()->getIO();
        $this->eventDispatcher = $this->getContainer()->get('dispatcher');
        $this->skipCoverage = \strlen(trim($input->getOption('coverage'))) > 0;
    }

    /**
     * @param int|string $loggingLevel
     */
    private function checkLoggingLevels($loggingLevel)
    {
        if (in_array($loggingLevel, LogVerbosity::ALLOWED_OPTIONS, false)) {
            return;
        }

        $this->io->note('Running infection with an unknown log-verbosity option, falling back to \'default\' option');
        $this->input->setOption('log-verbosity', LogVerbosity::NORMAL);
    }
}
