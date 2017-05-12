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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ConfigureCommand extends Command
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
        $questionHelper = new QuestionHelper();
        $question = new Question('asfasdf?');

        $result = $questionHelper->ask($input, $output, $question);

        var_dump('result =', $result);

        return 0;
    }

    protected function configure()
    {
        $this
            ->setName('configure')
            ->setDescription('Configure ....')
        ;
    }
}