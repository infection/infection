<?php

declare(strict_types=1);

namespace Infection\Command;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Listener\MutationConsoleLoggerSubscriber;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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
        $c = $this->getApplication()->find('configure');

        $result = $c->run(new ArrayInput([]), $output);

        var_dump($result);

//        $this->container['infection.config'] = 1;

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get('dispatcher');

        $initialTestsProgressBar = new ProgressBar($output);
        $initialTestsProgressBar->setFormat('verbose');

        $eventDispatcher->addSubscriber(new InitialTestsConsoleLoggerSubscriber($output, $initialTestsProgressBar));
        $eventDispatcher->addSubscriber(new MutationConsoleLoggerSubscriber($output, new ProgressBar($output)));

        $adapter = $this->get('test.framework.factory')->create($input->getOption('test-framework'));
        $processBuilder = new ProcessBuilder($adapter, $this->get('infection.config')->getProcessTimeout());

        // TODO add setFormatter
        $initialTestsRunner = new InitialTestsRunner($processBuilder, $eventDispatcher, $this->get('coverage.data'));
        $result = $initialTestsRunner->run();

        if (! $result->isSuccessful()) {
            $output->writeln(
                sprintf(
                    '<error>Tests do not pass. Error code %d. "%s". STDERR: %s</error>',
                    $result->getExitCode(),
                    $result->getExitCodeText(),
                    $result->getErrorOutput()
                )
            );
            return 1;
        }

        $output->writeln('Start mutation testing...');

        $onlyCovered = $input->getOption('only-covered');
        $filesFilter = $input->getOption('filter');
        $mutationsGenerator = new MutationsGenerator($this->get('src.dirs'), $this->get('exclude.dirs'), $result->getCodeCoverageData());
        $mutations = $mutationsGenerator->generate($onlyCovered, $filesFilter);

        $threadCount = (int) $input->getOption('threads');
        $parallelProcessManager = $this->get('parallel.process.runner');
        $mutantCreator = $this->get('mutant.creator');

        $mutationTestingRunner = new MutationTestingRunner($processBuilder, $parallelProcessManager, $mutantCreator, $eventDispatcher, $mutations);
        $mutationTestingRunner->run($threadCount);

        return 0;
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
                'filter',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter which files to mutate',
                ''
            )
        ;
    }

    private function get($name)
    {
        return $this->container[$name];
    }
}