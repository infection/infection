<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Config\ValueProvider;

use function count;
use function in_array;
use Infection\Config\ConsoleHelper;
use Infection\Locator\Locator;
use Infection\Locator\RootsFileOrDirectoryLocator;
use function Safe\glob;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class ExcludeDirsProvider
{
    public const EXCLUDED_ROOT_DIRS = ['vendor', 'tests', 'test'];

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
                if (in_array($dir, $dirsInCurrentDir, true)) {
                    $excludedDirs[] = $dir;
                }
            }

            $autocompleteValues = $dirsInCurrentDir;
        } elseif (count($sourceDirs) === 1) {
            $globDirs = array_filter(glob($sourceDirs[0] . '/*'), 'is_dir');

            $autocompleteValues = array_map(
                static function (string $dir) use ($sourceDirs) {
                    return str_replace($sourceDirs[0] . '/', '', $dir);
                },
                $globDirs
            );
        }

        $question = new Question($questionText, '');
        $question->setAutocompleterValues($autocompleteValues);
        $question->setValidator($this->getValidator(new RootsFileOrDirectoryLocator($sourceDirs, $this->filesystem)));

        while ($dir = $this->questionHelper->ask($input, $output, $question)) {
            if ($dir) {
                $excludedDirs[] = $dir;
            }
        }

        return array_values(array_unique($excludedDirs));
    }

    private function getValidator(Locator $locator)
    {
        return static function ($answer) use ($locator) {
            if (!$answer || strpos($answer, '*') !== false) {
                return $answer;
            }

            $locator->locate($answer);

            return $answer;
        };
    }
}
