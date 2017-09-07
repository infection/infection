<?php

/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection;

use Infection\Console\Exception\InvalidOptionException;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\EventDispatcher\EventDispatcher;
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
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Pimple\Container;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;

class InfectionApplication
{
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
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get('dispatcher');
        $testFrameworkKey = $this->input->getOption('test-framework');
        $adapter = $this->get('test.framework.factory')->create($testFrameworkKey);

        $metricsCalculator = new MetricsCalculator($adapter);

        $this->addSubscribers($eventDispatcher, $metricsCalculator, $adapter);

        $processBuilder = new ProcessBuilder($adapter, $this->get('infection.config')->getProcessTimeout());

        $initialTestsRunner = new InitialTestsRunner($processBuilder, $eventDispatcher);
        $initialTestSuitProcess = $initialTestsRunner->run();

        if (!$initialTestSuitProcess->isSuccessful()) {
            $this->logInitialTestsDoNotPass($initialTestSuitProcess);

            return 1;
        }

        $codeCoverageData = $this->getCodeCoverageData($testFrameworkKey);
        $mutationsGenerator = new MutationsGenerator(
            $this->get('src.dirs'),
            $this->get('exclude.paths'),
            $codeCoverageData,
            $this->getDefaultMutators(),
            $this->parseMutators($this->input->getOption('mutators')),
            $eventDispatcher
        );
        $mutations = $mutationsGenerator->generate($this->input->getOption('only-covered'), $this->input->getOption('filter'));

        $parallelProcessManager = $this->get('parallel.process.runner');
        $mutantCreator = $this->get('mutant.creator');
        $threadCount = (int) $this->input->getOption('threads');

        $mutationTestingRunner = new MutationTestingRunner($processBuilder, $parallelProcessManager, $mutantCreator, $eventDispatcher, $mutations);
        $mutationTestingRunner->run($threadCount, $codeCoverageData);

        if ($this->hasBadMsi($metricsCalculator)) {
            $io->error($this->getBadMsiErrorMessage($metricsCalculator));

            return 1;
        };

        return 0;
    }

    /**
     * Shortcut for container getter
     *
     * @param string $name
     *
     * @return mixed
     */
    private function get(string $name)
    {
        return $this->container[$name];
    }

    /**
     * Checks whether the container has particular service
     *
     * @param string $serviceId
     *
     * @return bool
     */
    private function has(string $serviceId)
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
        $eventDispatcher->addSubscriber(new TextFileLoggerSubscriber($this->get('infection.config'), $metricsCalculator, $this->get('filesystem')));
    }

    private function getCodeCoverageData(string $testFrameworkKey): CodeCoverageData
    {
        $coverageDir = $this->get(sprintf('coverage.dir.%s', $testFrameworkKey));
        $testFileDataProviderServiceId = sprintf('test.file.data.provider.%s', $testFrameworkKey);
        $testFileDataProviderService = $this->has($testFileDataProviderServiceId) ? $this->get($testFileDataProviderServiceId) : null;

        return new CodeCoverageData($coverageDir, new CoverageXmlParser($coverageDir), $testFileDataProviderService);
    }

    private function logInitialTestsDoNotPass(Process $initialTestSuitProcess)
    {
        $this->output->writeln(
            sprintf(
                '<error>Tests do not pass. Error code %d. "%s". STDERR: %s</error>',
                $initialTestSuitProcess->getExitCode(),
                $initialTestSuitProcess->getExitCodeText(),
                $initialTestSuitProcess->getErrorOutput()
            )
        );
    }

    private function hasBadMsi(MetricsCalculator $metricsCalculator): bool
    {
        if ($minMsi = (float) $this->input->getOption('min-msi')) {
            if ($metricsCalculator->getMutationScoreIndicator() < $minMsi) {
                return true;
            }
        }

        if ($minCoveredMsi = (float) $this->input->getOption('min-covered-msi')) {
            if ($metricsCalculator->getCoveredCodeMutationScoreIndicator() < $minCoveredMsi) {
                return true;
            }
        }

        return false;
    }

    private function getBadMsiErrorMessage(MetricsCalculator $metricsCalculator): string
    {
        $baseMessage = 'The minimum required %s percentage should be %s%%, but actual is %s%%. Improve your tests!';

        if ($minMsi = (float) $this->input->getOption('min-msi')) {
            return sprintf(
                $baseMessage,
                'MSI',
                $minMsi,
                $metricsCalculator->getMutationScoreIndicator()
            );
        }

        if ($minCoveredMsi = (float) $this->input->getOption('min-covered-msi')) {
            return sprintf(
                $baseMessage,
                'Covered Code MSI',
                $minCoveredMsi,
                $metricsCalculator->getCoveredCodeMutationScoreIndicator()
            );
        }

        throw new \LogicException('Seems like something is wrong with calculations and min-msi options.');
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

    private function getDefaultMutators()
    {
        return array_map(
            function (string $class): Mutator {
                return $this->container[$class];
            },
            Config\InfectionConfig::DEFAULT_MUTATORS
        );
    }
}
