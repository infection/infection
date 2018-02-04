<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\PhpUnitPathGuesser;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\TestFrameworkTypes;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class TestFrameworkConfigPathProvider
{
    /**
     * @var TestFrameworkConfigLocator
     */
    private $testFrameworkConfigLocator;
    /**
     * @var ConsoleHelper
     */
    private $consoleHelper;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    public function __construct(TestFrameworkConfigLocator $testFrameworkConfigLocator, ConsoleHelper $consoleHelper, QuestionHelper $questionHelper)
    {
        $this->testFrameworkConfigLocator = $testFrameworkConfigLocator;
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
    }

    public function get(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir, string $testFramework)
    {
        try {
            $this->testFrameworkConfigLocator->locate($testFramework);

            return null;
        } catch (\Exception $e) {
            if ($testFramework !== TestFrameworkTypes::PHPUNIT) {
                return $this->askTestFrameworkConfigLocation($input, $output, $dirsInCurrentDir, $testFramework, null);
            }

            if (!file_exists('composer.json')) {
                return $this->askTestFrameworkConfigLocation($input, $output, $dirsInCurrentDir, $testFramework, null);
            }

            $phpUnitPathGuesser = new PhpUnitPathGuesser(json_decode(file_get_contents('composer.json')));
            $defaultValue = $phpUnitPathGuesser->guess();

            if ($defaultValue) {
                try {
                    $this->testFrameworkConfigLocator->locate($testFramework, $defaultValue);

                    return $defaultValue;
                } catch (\Exception $e) {
                    // just continue to ask question
                }
            }

            return $this->askTestFrameworkConfigLocation($input, $output, $dirsInCurrentDir, $testFramework, $defaultValue);
        }
    }

    private function getValidator(string $testFramework): \Closure
    {
        return function (string $answerDir) use ($testFramework): string {
            $answerDir = trim($answerDir);

            if (!$answerDir) {
                return $answerDir;
            }

            if (!is_dir($answerDir)) {
                throw new \RuntimeException(sprintf('Could not find "%s" directory.', $answerDir));
            }

            $this->testFrameworkConfigLocator->locate($testFramework, $answerDir);

            return $answerDir;
        };
    }

    private function askTestFrameworkConfigLocation(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir, string $testFramework, $defaultValue): string
    {
        $question = sprintf(
            'Where is your <comment>%s.(xml|yml)(.dist)</comment> configuration located?',
            $testFramework
        );
        $questionText = $this->consoleHelper->getQuestion($question, $defaultValue);

        $question = new Question($questionText, $defaultValue);
        $question->setAutocompleterValues($dirsInCurrentDir);
        $question->setValidator($this->getValidator($testFramework));

        $testFrameworkConfigLocation = $this->questionHelper->ask($input, $output, $question);

        return (string) $testFrameworkConfigLocation;
    }
}
