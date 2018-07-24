<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @internal
 */
final class TextLogFileProvider
{
    public const TEXT_LOG_FILE_NAME = 'infection-log.txt';

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

    public function get(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir): string
    {
        $output->writeln(['']);

        $questionText = $this->consoleHelper->getQuestion(
            'Where do you want to store the text log file?',
            self::TEXT_LOG_FILE_NAME
        );

        $question = new Question($questionText, self::TEXT_LOG_FILE_NAME);
        $question->setAutocompleterValues($dirsInCurrentDir);

        return $this->questionHelper->ask($input, $output, $question);
    }
}
