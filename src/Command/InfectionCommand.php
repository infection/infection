<?php

declare(strict_types=1);

namespace Infection\Command;

use Infection\Differ\Differ;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutant\MutantCreator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
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
        $tempDir = $tempDirCreator->createAndGet();
        $testFrameworkFactory = new Factory($tempDir);
        $adapter = $testFrameworkFactory->create($input->getOption('test-framework'));

        $processBuilder = new ProcessBuilder($adapter);
        $process = $processBuilder->build();

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
        $mutantGenerator = new MutationsGenerator('src');
        $mutations = $mutantGenerator->generate();

        $mutantCreator = new MutantCreator($tempDir, new Differ());
        $mutationTestingRunner = new MutationTestingRunner($processBuilder, $mutantCreator, $mutations);
        $mutationTestingRunner->run();

        var_dump('tempdir=' . $tempDir);
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