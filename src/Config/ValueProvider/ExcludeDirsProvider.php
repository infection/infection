<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Finder\Locator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class ExcludeDirsProvider
{
    const EXCLUDED_ROOT_DIRS = ['vendor', 'tests', 'test'];

    /**
     * @var ConsoleHelper
     */
    private $consoleHelper;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(ConsoleHelper $consoleHelper, QuestionHelper $questionHelper, Filesystem $filesystem)
    {
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
        $this->filesystem = $filesystem;
    }

    public function get(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir, array $sourceDirs): array
    {
        $output->writeln([
            '',
            'There can be situations when you want to exclude some folders from generating mutants.',
            'You can use glob pattern (<comment>*Bundle/**/*/Tests</comment>) for them or just regular dir path.',
            'It should be <comment>relative</comment> to the source directory.',
            '<comment>You should not mutate test suite files.</comment>',
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
            foreach (self::EXCLUDED_ROOT_DIRS as $dir) {
                if (\in_array($dir, $dirsInCurrentDir, true)) {
                    $excludedDirs[] = $dir;
                }
            }

            $autocompleteValues = $dirsInCurrentDir;
        } elseif (\count($sourceDirs) === 1) {
            $globDirs = \array_filter(\glob($sourceDirs[0] . '/*'), 'is_dir');

            $autocompleteValues = \array_map(
                function (string $dir) use ($sourceDirs) {
                    return \str_replace($sourceDirs[0] . '/', '', $dir);
                },
                $globDirs
            );
        }

        $question = new Question($questionText, '');
        $question->setAutocompleterValues($autocompleteValues);
        $question->setValidator($this->getValidator(new Locator($sourceDirs, $this->filesystem)));

        while ($dir = $this->questionHelper->ask($input, $output, $question)) {
            if ($dir) {
                $excludedDirs[] = $dir;
            }
        }

        return \array_unique($excludedDirs);
    }

    private function getValidator(Locator $locator)
    {
        return function ($answer) use ($locator) {
            if (!$answer || \strpos($answer, '*') !== false) {
                return $answer;
            }

            $locator->locate($answer);

            return $answer;
        };
    }
}
