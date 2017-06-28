<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Finder\Locator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ExcludeDirsProvider
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

    public function get(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir, array $sourceDirs): array
    {
        $output->writeln([
            '',
            'There can be situations when you want to exclude some folders from generating mutants.',
            'You can use glob pattern (<comment>*Bundle/**/*/Tests</comment>) for them or just regular dir path.',
            'It should be <comment>relative</comment> to the source directory.',
            'Press <comment><return></comment> to stop/skip adding dirs.',
            '',
        ]);

        $autocompleteValues = [];
        $questionText = $this->consoleHelper->getQuestion(
            'Any directories to exclude from within your source directories?',
            ''
        );
        $excludedDirs = [];

        if ($sourceDirs === ['.']) {
            if (is_dir('vendor')) {
                $excludedDirs[] = 'vendor';
            }

            $autocompleteValues = $dirsInCurrentDir;
        } elseif (count($sourceDirs) === 1) {
            $globDirs = array_filter(glob($sourceDirs[0] .'/*'), 'is_dir');

            $autocompleteValues = array_map(
                function (string $dir) use ($sourceDirs) {
                    return str_replace($sourceDirs[0] . '/', '', $dir);
                },
                $globDirs
            );
        }

        $locator = new Locator($sourceDirs);

        $question = new Question($questionText, '');
        $question->setAutocompleterValues($autocompleteValues);
        $question->setValidator($this->getValidator($locator));

        while ($dir = $this->questionHelper->ask($input, $output, $question)) {

            if ($dir) {
                $excludedDirs[] = $dir;
            }
        }

        return array_unique($excludedDirs);
    }

    private function getValidator(Locator $locator)
    {
        return function ($answer) use ($locator) {
            if (!$answer || strpos($answer, '*') !== false) {
                return $answer;
            }

            $locator->locate($answer);

            return $answer;
        };
    }
}