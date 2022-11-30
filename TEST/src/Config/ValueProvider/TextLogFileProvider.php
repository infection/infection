<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Config\ValueProvider;

use _HumbugBox9658796bb9f0\Infection\Config\ConsoleHelper;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\QuestionHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\Question;
final class TextLogFileProvider
{
    private ConsoleHelper $consoleHelper;
    private QuestionHelper $questionHelper;
    public function __construct(ConsoleHelper $consoleHelper, QuestionHelper $questionHelper)
    {
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
    }
    public function get(IO $io, array $dirsInCurrentDir) : ?string
    {
        $io->writeln(['']);
        $io->writeln(['', 'Infection may save execution results in a text log for a future review.', 'This can be "infection.log" but we recommend leaving it out for performance reasons.', 'Press <comment><return></comment> to skip additional logging.', '']);
        $questionText = $this->consoleHelper->getQuestion('Where do you want to store the text log file?', '');
        $question = new Question($questionText, '');
        $question->setAutocompleterValues($dirsInCurrentDir);
        $answer = $this->questionHelper->ask($io->getInput(), $io->getOutput(), $question);
        return $answer === '' ? null : $answer;
    }
}
