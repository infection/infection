<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class SourceDirsProvider
{
    /**
     * @var ConsoleHelper
     */
    private $consoleHelper;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    /**
     * @var SourceDirGuesser
     */
    private $sourceDirGuesser;

    public function __construct(ConsoleHelper $consoleHelper, QuestionHelper $questionHelper, SourceDirGuesser $sourceDirGuesser)
    {
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
        $this->sourceDirGuesser = $sourceDirGuesser;
    }

    public function get(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir): array
    {
        $output->writeln(['']);

        $guessedSourceDirs = (array) $this->sourceDirGuesser->guess();

        $choices = array_unique(array_merge(['.'], array_values($dirsInCurrentDir), $guessedSourceDirs));

        $defaultValues = $guessedSourceDirs ? implode(',', $guessedSourceDirs) : null;

        $questionText = $this->consoleHelper->getQuestion(
            'Which source directories do you want to include (comma separated)?',
            $defaultValues
        );

        $question = new ChoiceQuestion($questionText, $choices, $defaultValues);
        $question->setMultiselect(true);

        $sourceFolders = $this->questionHelper->ask($input, $output, $question);

        if (in_array('.', $sourceFolders, true) && count($sourceFolders) > 1) {
            throw new \LogicException('You cannot use current folder "." with other subfolders');
        }

        return $sourceFolders;
    }
}
