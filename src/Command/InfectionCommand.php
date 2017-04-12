<?php

declare(strict_types=1);

namespace Infection\Command;

use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\TestFramework\Factory;
use Infection\Utils\TempDirectoryCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfectionCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tempDirCreator = new TempDirectoryCreator();
        $testFrameworkFactory = new Factory($tempDirCreator->createAndGet());
        $adapter = $testFrameworkFactory->create($input->getOption('test-framework'));

        $processBuilder = new ProcessBuilder($adapter);
        $process = $processBuilder->getProcess();

        $initialTestsRunner = new InitialTestsRunner($process, $output);
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
        ;
    }
}