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

use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function implode;
use function in_array;
use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Console\IO;
use LogicException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * @internal
 */
final readonly class SourceDirsProvider
{
    public function __construct(private ConsoleHelper $consoleHelper, private QuestionHelper $questionHelper, private SourceDirGuesser $sourceDirGuesser)
    {
    }

    /**
     * @param string[] $dirsInCurrentDir
     *
     * @return string[]
     */
    public function get(IO $io, array $dirsInCurrentDir): array
    {
        $io->newLine();

        $guessedSourceDirs = (array) $this->sourceDirGuesser->guess();

        $choices = array_unique(array_merge(['.'], array_values($dirsInCurrentDir), $guessedSourceDirs));

        $defaultValues = $guessedSourceDirs !== [] ? implode(',', $guessedSourceDirs) : null;

        $questionText = $this->consoleHelper->getQuestion(
            'Which source directories do you want to include (comma separated)?',
            $defaultValues,
        );

        $question = new ChoiceQuestion($questionText, $choices, $defaultValues);
        $question->setMultiselect(true);

        $sourceFolders = $this->questionHelper->ask($io->getInput(), $io->getOutput(), $question);

        if (in_array('.', $sourceFolders, true) && count($sourceFolders) > 1) {
            throw new LogicException('You cannot use current folder "." with other subfolders');
        }

        return $sourceFolders;
    }
}
