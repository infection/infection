<?php

declare(strict_types=1);

namespace Infection\Command;

use Infection\Differ\Differ;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutant\MutantCreator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\TestFramework\Factory;
use Infection\Utils\TempDirectoryCreator;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
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
        $adapter = $this->get('test.framework.factory')->create($input->getOption('test-framework'));
        $processBuilder = new ProcessBuilder($adapter);

        $initialTestsRunner = new InitialTestsRunner($processBuilder, $output);
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
        }

        // generate mutation
        $mutations =$this->get('mutations.generator')->generate();

        $threadCount = (int) $input->getOption('threads');
        $parallelProcessManager = new ParallelProcessRunner($threadCount);
        $mutantCreator = $this->get('mutant.creator');
        $mutationTestingRunner = new MutationTestingRunner($processBuilder, $parallelProcessManager, $mutantCreator, $mutations);
        $mutationTestingRunner->run();
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
        ;
    }

    private function get($name)
    {
        return $this->container[$name];
    }
}