<?php

declare(strict_types=1);

namespace Infection;

use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationConsoleLoggerSubscriber;
use Infection\Process\Listener\TextFileLoggerSubscriber;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Pimple\Container;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get('dispatcher');
        $testFrameworkKey = $this->input->getOption('test-framework');
        $adapter = $this->get('test.framework.factory')->create($testFrameworkKey);

        $initialTestsProgressBar = new ProgressBar($this->output);
        $initialTestsProgressBar->setFormat('verbose');

        $metricsCalculator = new MetricsCalculator($adapter);

        $this->addSubscribers($eventDispatcher, $initialTestsProgressBar, $metricsCalculator);

        $processBuilder = new ProcessBuilder($adapter, $this->get('infection.config')->getProcessTimeout());

        $initialTestsRunner = new InitialTestsRunner($processBuilder, $eventDispatcher);
        $initialTestSuitProcess = $initialTestsRunner->run();

        if (! $initialTestSuitProcess->isSuccessful()) {
            $this->logInitialTestsDoNotPass($initialTestSuitProcess);

            return 1;
        }

        $this->output->writeln(['', 'Generate mutants...', '']);

        $codeCoverageData = $this->getCodeCoverageData($testFrameworkKey);
        $mutationsGenerator = new MutationsGenerator($this->get('src.dirs'), $this->get('exclude.dirs'), $codeCoverageData);
        $mutations = $mutationsGenerator->generate($this->input->getOption('only-covered'), $this->input->getOption('filter'));

        $parallelProcessManager = $this->get('parallel.process.runner');
        $mutantCreator = $this->get('mutant.creator');
        $threadCount = (int) $this->input->getOption('threads');

        $mutationTestingRunner = new MutationTestingRunner($processBuilder, $parallelProcessManager, $mutantCreator, $eventDispatcher, $mutations);
        $mutationTestingRunner->run($threadCount, $codeCoverageData);

        return 0;
    }

    /**
     * Shortcut for container getter
     *
     * @param string $name
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

    private function addSubscribers(EventDispatcher $eventDispatcher, ProgressBar $initialTestsProgressBar, MetricsCalculator $metricsCalculator): void
    {
        $eventDispatcher->addSubscriber(new InitialTestsConsoleLoggerSubscriber($this->output, $initialTestsProgressBar));
        $eventDispatcher->addSubscriber(new MutationConsoleLoggerSubscriber($this->output, $this->getOutputFormatter(), $metricsCalculator, $this->get('diff.colorizer'), $this->input->getOption('show-mutations')));
        $eventDispatcher->addSubscriber(new TextFileLoggerSubscriber($this->get('infection.config'), $metricsCalculator));
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
}