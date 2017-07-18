<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);

namespace Infection\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SourceDirsProvider
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

    public function get(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir): array
    {
        $output->writeln(['']);

        $guessedSourceDirs = null;

        if (file_exists('composer.json')) {
            $sourceDirGuesser = new SourceDirGuesser(
                json_decode(file_get_contents('composer.json'))
            );
            $guessedSourceDirs = $sourceDirGuesser->guess();
        }

        $defaultValues = $guessedSourceDirs ? implode(',', $guessedSourceDirs) : null;

        $questionText = $this->consoleHelper->getQuestion(
            'What source directories do you want to include (comma separated)?',
            $defaultValues
        );

        $choices = array_merge(['.'], array_values($dirsInCurrentDir));

        $question = new ChoiceQuestion($questionText, $choices, $defaultValues);
        $question->setMultiselect(true);

        $sourceFolders = $this->questionHelper->ask($input, $output, $question);

        if (in_array('.', $sourceFolders, true) && count($sourceFolders) > 1) {
            throw new \LogicException('You cannot use current folder "." with other subfolders');
        }

        return $sourceFolders;
    }
}