<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Config\ValueProvider;

use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function implode;
use function in_array;
use _HumbugBox9658796bb9f0\Infection\Config\ConsoleHelper;
use _HumbugBox9658796bb9f0\Infection\Config\Guesser\SourceDirGuesser;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use LogicException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\QuestionHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\ChoiceQuestion;
final class SourceDirsProvider
{
    private ConsoleHelper $consoleHelper;
    private QuestionHelper $questionHelper;
    private SourceDirGuesser $sourceDirGuesser;
    public function __construct(ConsoleHelper $consoleHelper, QuestionHelper $questionHelper, SourceDirGuesser $sourceDirGuesser)
    {
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
        $this->sourceDirGuesser = $sourceDirGuesser;
    }
    public function get(IO $io, array $dirsInCurrentDir) : array
    {
        $io->newLine();
        $guessedSourceDirs = (array) $this->sourceDirGuesser->guess();
        $choices = array_unique(array_merge(['.'], array_values($dirsInCurrentDir), $guessedSourceDirs));
        $defaultValues = $guessedSourceDirs !== [] ? implode(',', $guessedSourceDirs) : null;
        $questionText = $this->consoleHelper->getQuestion('Which source directories do you want to include (comma separated)?', $defaultValues);
        $question = new ChoiceQuestion($questionText, $choices, $defaultValues);
        $question->setMultiselect(\true);
        $sourceFolders = $this->questionHelper->ask($io->getInput(), $io->getOutput(), $question);
        if (in_array('.', $sourceFolders, \true) && count($sourceFolders) > 1) {
            throw new LogicException('You cannot use current folder "." with other subfolders');
        }
        return $sourceFolders;
    }
}
