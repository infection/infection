<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection;

use Infection\Console\Exception\InfectionException;
use Infection\Console\Exception\InvalidOptionException;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Mutant\Exception\MsiCalculationException;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutator\Mutator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\Process\Listener\MutantCreatingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationGeneratingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationTestingConsoleLoggerSubscriber;
use Infection\Process\Listener\TextFileLoggerSubscriber;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpSpec\PhpSpecExtraOptions;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\PhpUnit\PhpUnitExtraOptions;
use Infection\TestFramework\TestFrameworkExtraOptions;
use Infection\TestFramework\TestFrameworkTypes;
use Pimple\Container;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;

class InfectionApplication
{
    const CI_FLAG_ERROR = 'The minimum required %s percentage should be %s%%, but actual is %s%%. Improve your tests!';

    const LOGO = <<<'ASCII'
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____ 
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/
 
ASCII;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(Container $container, InputInterface $input, OutputInterface $output)
    {
        $this->container = $container;
        $this->input = $input;
        $this->output = $output;
    }

    public function run()
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $io->writeln(self::LOGO);

        try {
            /** @var EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->get('dispatcher');
            $testFrameworkKey = $this->input->getOption('test-framework');
            $adapter = $this->get('test.framework.factory')->create($testFrameworkKey);

            $metricsCalculator = new MetricsCalculator();

            $this->addSubscribers($eventDispatcher, $metricsCalculator, $adapter);

            $processBuilder = new ProcessBuilder($adapter, $this->get('infection.config')->getProcessTimeout());
            $testFrameworkOptions = $this->getTestFrameworkExtraOptions($testFrameworkKey);

            $initialTestsRunner = new InitialTestsRunner($processBuilder, $eventDispatcher);
            $initialTestSuitProcess = $initialTestsRunner->run($testFrameworkOptions->getForInitialProcess());

            if (!$initialTestSuitProcess->isSuccessful()) {
                $this->logInitialTestsDoNotPass($io, $initialTestSuitProcess, $testFrameworkKey);

                return 1;
            }

            $codeCoverageData = $this->getCodeCoverageData($testFrameworkKey);
            $mutationsGenerator = new MutationsGenerator(
                $this->get('src.dirs'),
                $this->get('exclude.paths'),
                $codeCoverageData,
                $this->getDefaultMutators(),
                $this->parseMutators($this->input->getOption('mutators')),
                $eventDispatcher,
                $this->get('parser')
            );
            $mutations = $mutationsGenerator->generate($this->input->getOption('only-covered'), $this->input->getOption('filter'));

            $parallelProcessManager = $this->get('parallel.process.runner');
            $mutantCreator = $this->get('mutant.creator');
            $threadCount = (int) $this->input->getOption('threads');

            $mutationTestingRunner = new MutationTestingRunner($processBuilder, $parallelProcessManager, $mutantCreator, $eventDispatcher, $mutations);
            $mutationTestingRunner->run($threadCount, $codeCoverageData, $testFrameworkOptions->getForMutantProcess());

            if ($this->hasBadMsi($metricsCalculator)) {
                $io->error($this->getBadMsiErrorMessage($metricsCalculator));

                return 1;
            }

            if ($this->hasBadCoveredMsi($metricsCalculator)) {
                $io->error($this->getBadCoveredMsiErrorMessage($metricsCalculator));

                return 1;
            }

            return 0;
        } catch (InfectionException $e) {
            $io->error($e->getMessage());

            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $io->error($e->getTraceAsString());
            }

            return 1;
        }
    }

    private function get(string $name)
    {
        return $this->container[$name];
    }

    private function has(string $serviceId): bool
    {
        return isset($this->container[$serviceId]);
    }

    private function getOutputFormatter(): OutputFormatter
    {
        if ($this->input->getOption('formatter') === 'progress') {
            return new ProgressFormatter(new ProgressBar($this->output));
        }

        if ($this->input->getOption('formatter') === 'dot') {
            return new DotFormatter($this->output);
        }

        throw new \InvalidArgumentException('Incorrect formatter. Possible values: dot, progress');
    }

    private function addSubscribers(EventDispatcher $eventDispatcher, MetricsCalculator $metricsCalculator, AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $initialTestsProgressBar = new ProgressBar($this->output);
        $initialTestsProgressBar->setFormat('verbose');

        $mutationGeneratingProgressBar = new ProgressBar($this->output);
        $mutationGeneratingProgressBar->setFormat('Processing source code files: %current%/%max%');

        $mutantCreatingProgressBar = new ProgressBar($this->output);
        $mutantCreatingProgressBar->setFormat('Creating mutated files and processes: %current%/%max%');

        $eventDispatcher->addSubscriber(new InitialTestsConsoleLoggerSubscriber($this->output, $initialTestsProgressBar, $testFrameworkAdapter));
        $eventDispatcher->addSubscriber(new MutationGeneratingConsoleLoggerSubscriber($this->output, $mutationGeneratingProgressBar));
        $eventDispatcher->addSubscriber(new MutantCreatingConsoleLoggerSubscriber($this->output, $mutantCreatingProgressBar));
        $eventDispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber($this->output, $this->getOutputFormatter(), $metricsCalculator, $this->get('diff.colorizer'), $this->input->getOption('show-mutations')));
        $eventDispatcher->addSubscriber(new TextFileLoggerSubscriber($this->get('infection.config'), $metricsCalculator, $this->get('filesystem'), (int) $this->input->getOption('log-verbosity')));
    }

    private function getCodeCoverageData(string $testFrameworkKey): CodeCoverageData
    {
        $coverageDir = $this->get(sprintf('coverage.dir.%s', $testFrameworkKey));
        $testFileDataProviderServiceId = sprintf('test.file.data.provider.%s', $testFrameworkKey);
        $testFileDataProviderService = $this->has($testFileDataProviderServiceId) ? $this->get($testFileDataProviderServiceId) : null;

        return new CodeCoverageData($coverageDir, new CoverageXmlParser($coverageDir), $testFrameworkKey, $testFileDataProviderService);
    }

    private function logInitialTestsDoNotPass(SymfonyStyle $io, Process $initialTestSuitProcess, string $testFrameworkKey)
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

        $io->error($lines);
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

    private function getDefaultMutators(): array
    {
        return array_map(function (string $class): Mutator {
            return $this->container[$class];
        },
        Config\InfectionConfig::DEFAULT_MUTATORS);
    }

    private function getTestFrameworkExtraOptions(string $testFrameworkKey): TestFrameworkExtraOptions
    {
        $extraOptions = $this->input->getOption('test-framework-options');

        return TestFrameworkTypes::PHPUNIT === $testFrameworkKey
            ? new PhpUnitExtraOptions($extraOptions)
            : new PhpSpecExtraOptions($extraOptions);
    }
}
