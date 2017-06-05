<?php

declare(strict_types=1);

namespace Infection\Command;

use Infection\Config\ConsoleHelper;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Listener\MutationConsoleLoggerSubscriber;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\Process\Listener\TextFileLoggerSubscriber;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Config\InfectionConfig;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfectionCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO --min-msi 95
        // TODO google populating DI container by user's input
        $this->container['infection.config'] = function (Container $c) : InfectionConfig {
            try {
                $infectionConfigFile = $c['locator']->locateAnyOf(['infection.json', 'infection.json.dist']);
                $json = file_get_contents($infectionConfigFile);
            } catch (\Exception $e) {
                $json = '{}';
            }

            return new InfectionConfig(json_decode($json));
        };

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get('dispatcher');
        $adapter = $this->get('test.framework.factory')->create($input->getOption('test-framework'));

        $initialTestsProgressBar = new ProgressBar($output);
        $initialTestsProgressBar->setFormat('verbose');

        $metricsCalculator = new MetricsCalculator($adapter);

        $eventDispatcher->addSubscriber(new InitialTestsConsoleLoggerSubscriber($output, $initialTestsProgressBar));
        $eventDispatcher->addSubscriber(new MutationConsoleLoggerSubscriber($output, $this->getOutputFormatter($input, $output), $metricsCalculator, $this->get('diff.colorizer'), $input->getOption('show-mutations')));
        $eventDispatcher->addSubscriber(new TextFileLoggerSubscriber($this->get('infection.config'), $metricsCalculator));

        $processBuilder = new ProcessBuilder($adapter, $this->get('infection.config')->getProcessTimeout());

        // TODO add setFormatter
        $initialTestsRunner = new InitialTestsRunner($processBuilder, $eventDispatcher);
        $initialTestSuitProcess = $initialTestsRunner->run();

        if (! $initialTestSuitProcess->isSuccessful()) {
            $output->writeln(
                sprintf(
                    '<error>Tests do not pass. Error code %d. "%s". STDERR: %s</error>',
                    $initialTestSuitProcess->getExitCode(),
                    $initialTestSuitProcess->getExitCodeText(),
                    $initialTestSuitProcess->getErrorOutput()
                )
            );
            return 1;
        }

        $onlyCovered = $input->getOption('only-covered');
        $filesFilter = $input->getOption('filter');
        $codeCoverageData = new CodeCoverageData($this->get('coverage.dir'), $this->get('coverage.parser'));

        $output->writeln(['', 'Generate mutants...', '']);

        $mutationsGenerator = new MutationsGenerator($this->get('src.dirs'), $this->get('exclude.dirs'), $codeCoverageData);
        $mutations = $mutationsGenerator->generate($onlyCovered, $filesFilter);

        $threadCount = (int) $input->getOption('threads');
        $parallelProcessManager = $this->get('parallel.process.runner');
        $mutantCreator = $this->get('mutant.creator');

        $mutationTestingRunner = new MutationTestingRunner($processBuilder, $parallelProcessManager, $mutantCreator, $eventDispatcher, $mutations);
        $mutationTestingRunner->run($threadCount, $codeCoverageData);

        return 0;
    }

    /**
     * Run configuration command if config does not exist
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setOutputFormatterStyles($output);

        $configExists = file_exists(InfectionConfig::CONFIG_FILE_NAME) ||
            file_exists(InfectionConfig::CONFIG_FILE_NAME . '.dist');

        if (! $configExists) {
            $configureCommand = $this->getApplication()->find('configure');

            $args = [
                '--test-framework' => $input->getOption('test-framework')
            ];

            $result = $configureCommand->run(new ArrayInput($args), $output);

            if ($result !== 0) {
                throw new \Exception('Configuration aborted');
            }
        }

        if (!defined('HHVM_VERSION') && !extension_loaded('xdebug')) {
            throw new \Exception('You need to install and enable xDebug in order to allow for code coverage generation.');
        }
    }

    private function getOutputFormatter(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('formatter') === 'progress') {
            return new ProgressFormatter(new ProgressBar($output));
        }

        if ($input->getOption('formatter') === 'dot') {
            return new DotFormatter($output);
        }

        throw new \InvalidArgumentException('Incorrect formatter. Possible values: dot, progress');
    }

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Runs the mutation testing.')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the Test framework to use (phpunit, phpspec)',
                'phpunit'
            )
            ->addOption(
                'threads',
                null,
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
        ;
    }

    private function get($name)
    {
        return $this->container[$name];
    }

    private function setOutputFormatterStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('with-error', new OutputFormatterStyle('red'));
        $output->getFormatter()->setStyle('uncovered', new OutputFormatterStyle('blue', null, ['bold']));
        $output->getFormatter()->setStyle('timeout', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle('escaped', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('killed', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('code', new OutputFormatterStyle('white'));

        $output->getFormatter()->setStyle('diff-add', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('diff-del', new OutputFormatterStyle('red'));
    }
}