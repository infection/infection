<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Config\ValueProvider;

use Closure;
use Exception;
use function file_exists;
use _HumbugBox9658796bb9f0\Infection\Config\ConsoleHelper;
use _HumbugBox9658796bb9f0\Infection\Config\Guesser\PhpUnitPathGuesser;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use _HumbugBox9658796bb9f0\Infection\TestFramework\TestFrameworkTypes;
use function is_dir;
use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
use function _HumbugBox9658796bb9f0\Safe\json_decode;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\QuestionHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\Question;
use function trim;
final class TestFrameworkConfigPathProvider
{
    private TestFrameworkConfigLocatorInterface $testFrameworkConfigLocator;
    private ConsoleHelper $consoleHelper;
    private QuestionHelper $questionHelper;
    public function __construct(TestFrameworkConfigLocatorInterface $testFrameworkConfigLocator, ConsoleHelper $consoleHelper, QuestionHelper $questionHelper)
    {
        $this->testFrameworkConfigLocator = $testFrameworkConfigLocator;
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
    }
    public function get(IO $io, array $dirsInCurrentDir, string $testFramework) : ?string
    {
        try {
            $this->testFrameworkConfigLocator->locate($testFramework);
            return null;
        } catch (Exception $e) {
            if ($testFramework !== TestFrameworkTypes::PHPUNIT) {
                return $this->askTestFrameworkConfigLocation($io, $dirsInCurrentDir, $testFramework, '');
            }
            if (!file_exists('composer.json')) {
                return $this->askTestFrameworkConfigLocation($io, $dirsInCurrentDir, $testFramework, '');
            }
            $composerJsonText = file_get_contents('composer.json');
            $phpUnitPathGuesser = new PhpUnitPathGuesser(json_decode($composerJsonText));
            $defaultValue = $phpUnitPathGuesser->guess();
            if ($defaultValue !== '') {
                try {
                    $this->testFrameworkConfigLocator->locate($testFramework, $defaultValue);
                    return $defaultValue;
                } catch (Exception $e) {
                }
            }
            return $this->askTestFrameworkConfigLocation($io, $dirsInCurrentDir, $testFramework, $defaultValue);
        }
    }
    private function getValidator(string $testFramework) : Closure
    {
        return function (string $answerDir) use($testFramework) : string {
            $answerDir = trim($answerDir);
            if ($answerDir === '') {
                return $answerDir;
            }
            if (!is_dir($answerDir)) {
                throw new RuntimeException(sprintf('Could not find "%s" directory.', $answerDir));
            }
            $this->testFrameworkConfigLocator->locate($testFramework, $answerDir);
            return $answerDir;
        };
    }
    private function askTestFrameworkConfigLocation(IO $io, array $dirsInCurrentDir, string $testFramework, string $defaultValue) : string
    {
        $question = sprintf('Where is your <comment>%s.(xml|yml)(.dist)</comment> configuration located?', $testFramework);
        $questionText = $this->consoleHelper->getQuestion($question, $defaultValue);
        $question = new Question($questionText, $defaultValue);
        $question->setAutocompleterValues($dirsInCurrentDir);
        $question->setValidator($this->getValidator($testFramework));
        $testFrameworkConfigLocation = $this->questionHelper->ask($io->getInput(), $io->getOutput(), $question);
        return $testFrameworkConfigLocation;
    }
}
