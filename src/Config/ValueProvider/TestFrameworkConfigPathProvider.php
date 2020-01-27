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

use Closure;
use Exception;
use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\PhpUnitPathGuesser;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\TestFrameworkTypes;
use RuntimeException;
use function Safe\file_get_contents;
use function Safe\json_decode;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @internal
 */
final class TestFrameworkConfigPathProvider
{
    private $testFrameworkConfigLocator;
    private $consoleHelper;
    private $questionHelper;

    public function __construct(TestFrameworkConfigLocatorInterface $testFrameworkConfigLocator, ConsoleHelper $consoleHelper, QuestionHelper $questionHelper)
    {
        $this->testFrameworkConfigLocator = $testFrameworkConfigLocator;
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
    }

    /**
     * @param array<string> $dirsInCurrentDir
     */
    public function get(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir, string $testFramework): ?string
    {
        try {
            $this->testFrameworkConfigLocator->locate($testFramework);

            return null;
        } catch (Exception $e) {
            if ($testFramework !== TestFrameworkTypes::PHPUNIT) {
                return $this->askTestFrameworkConfigLocation($input, $output, $dirsInCurrentDir, $testFramework, '');
            }

            if (!file_exists('composer.json')) {
                return $this->askTestFrameworkConfigLocation($input, $output, $dirsInCurrentDir, $testFramework, '');
            }

            $composerJsonText = file_get_contents('composer.json');

            $phpUnitPathGuesser = new PhpUnitPathGuesser(json_decode($composerJsonText));
            $defaultValue = $phpUnitPathGuesser->guess();

            if ($defaultValue) {
                try {
                    $this->testFrameworkConfigLocator->locate($testFramework, $defaultValue);

                    return $defaultValue;
                } catch (Exception $e) {
                    // just continue to ask question
                }
            }

            return $this->askTestFrameworkConfigLocation($input, $output, $dirsInCurrentDir, $testFramework, $defaultValue);
        }
    }

    private function getValidator(string $testFramework): Closure
    {
        return function (string $answerDir) use ($testFramework): string {
            $answerDir = trim($answerDir);

            if (!$answerDir) {
                return $answerDir;
            }

            if (!is_dir($answerDir)) {
                throw new RuntimeException(sprintf('Could not find "%s" directory.', $answerDir));
            }

            $this->testFrameworkConfigLocator->locate($testFramework, $answerDir);

            return $answerDir;
        };
    }

    /**
     * @param array<string> $dirsInCurrentDir
     */
    private function askTestFrameworkConfigLocation(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir, string $testFramework, string $defaultValue): string
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

        return $testFrameworkConfigLocation;
    }
}
