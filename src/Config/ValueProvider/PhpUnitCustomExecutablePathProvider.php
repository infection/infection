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
use const DIRECTORY_SEPARATOR;
use Infection\Config\ConsoleHelper;
use Infection\Finder\Exception\FinderException;
use Infection\Finder\TestFrameworkFinder;
use RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @internal
 */
final class PhpUnitCustomExecutablePathProvider
{
    /**
     * @var TestFrameworkFinder
     */
    private $phpUnitExecutableFinder;
    /**
     * @var ConsoleHelper
     */
    private $consoleHelper;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    public function __construct(TestFrameworkFinder $phpUnitExecutableFinder, ConsoleHelper $consoleHelper, QuestionHelper $questionHelper)
    {
        $this->phpUnitExecutableFinder = $phpUnitExecutableFinder;
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
    }

    public function get(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->phpUnitExecutableFinder->find();
        } catch (FinderException $e) {
            $output->writeln(['']);

            $questionText = $this->consoleHelper->getQuestion(
                'We did not find phpunit executable. Please provide custom absolute path'
            );

            $question = new Question($questionText);
            $question->setValidator($this->getValidator());

            return str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                $this->questionHelper->ask($input, $output, $question)
            );
        }

        return null;
    }

    private function getValidator(): Closure
    {
        return static function ($answerPath) {
            $answerPath = $answerPath ? trim($answerPath) : $answerPath;

            if (!$answerPath || !file_exists($answerPath)) {
                throw new RuntimeException(sprintf('Custom path "%s" is incorrect.', $answerPath));
            }

            return $answerPath;
        };
    }
}
