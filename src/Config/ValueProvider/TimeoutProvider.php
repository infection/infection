<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\InfectionConfig;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class TimeoutProvider
{
    /**
     * @var ConsoleHelper
     */
    private $consoleHelper;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    public function __construct(ConsoleHelper $consoleHelper, QuestionHelper $questionHelper)
    {
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
    }

    public function get(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['']);

        $questionText = $this->consoleHelper->getQuestion('Single test suite timeout in seconds', InfectionConfig::PROCESS_TIMEOUT_SECONDS);

        $timeoutQuestion = new Question($questionText, InfectionConfig::PROCESS_TIMEOUT_SECONDS);
        $timeoutQuestion->setValidator(function ($answer) {
            if (!$answer || !is_numeric($answer) || (int) $answer <= 0) {
                throw new \RuntimeException('Timeout should be an integer greater than 0');
            }

            return (int) $answer;
        });

        return $this->questionHelper->ask($input, $output, $timeoutQuestion);
    }
}
