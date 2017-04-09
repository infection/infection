<?php

namespace Infection\Command;

use Infection\Process\Runner\InitialTestsRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class InfectionCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Runs the mutation testing.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processBuilder = new ProcessBuilder(['vendor/phpunit/phpunit/phpunit']);
        $process = $processBuilder->getProcess();

        $initialTestsRunner = new InitialTestsRunner($process, $output);
        $result = $initialTestsRunner->run();

        if (!$result->isSuccessful()) {
            $output->writeln(sprintf('<error>Tests do not pass. Error code %d</error>', $result->getExitCode()));
        }
    }
}