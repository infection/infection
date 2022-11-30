<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Config\ValueProvider;

use Closure;
use const DIRECTORY_SEPARATOR;
use function file_exists;
use _HumbugBox9658796bb9f0\Infection\Config\ConsoleHelper;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\Exception\FinderException;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\TestFrameworkFinder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\TestFrameworkTypes;
use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_replace;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\QuestionHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\Question;
use function trim;
final class PhpUnitCustomExecutablePathProvider
{
    private TestFrameworkFinder $phpUnitExecutableFinder;
    private ConsoleHelper $consoleHelper;
    private QuestionHelper $questionHelper;
    public function __construct(TestFrameworkFinder $phpUnitExecutableFinder, ConsoleHelper $consoleHelper, QuestionHelper $questionHelper)
    {
        $this->phpUnitExecutableFinder = $phpUnitExecutableFinder;
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
    }
    public function get(IO $io) : ?string
    {
        try {
            $this->phpUnitExecutableFinder->find(TestFrameworkTypes::PHPUNIT);
        } catch (FinderException $e) {
            $io->writeln(['']);
            $questionText = $this->consoleHelper->getQuestion('We did not find phpunit executable. Please provide custom absolute path');
            $question = new Question($questionText);
            $question->setValidator($this->getValidator());
            return str_replace(DIRECTORY_SEPARATOR, '/', $this->questionHelper->ask($io->getInput(), $io->getOutput(), $question));
        }
        return null;
    }
    private function getValidator() : Closure
    {
        return static function ($answerPath) : string {
            $answerPath = $answerPath !== '' ? trim($answerPath) : $answerPath;
            if ($answerPath === '' || !file_exists($answerPath)) {
                throw new RuntimeException(sprintf('Custom path "%s" is incorrect.', $answerPath));
            }
            return $answerPath;
        };
    }
}
