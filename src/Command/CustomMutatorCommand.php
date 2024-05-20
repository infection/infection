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

namespace Infection\Command;

use Infection\Console\IO;
use function iterator_to_array;
use RuntimeException;
use function Safe\getcwd;
use function sprintf;
use function str_replace;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;
use function trim;
use function ucfirst;

/**
 * @internal
 */
final class CustomMutatorCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('custom-mutator')
            ->setDescription('Creates a cusotm mutator')
            ->addArgument('Mutator name', InputArgument::OPTIONAL);
    }

    protected function executeCommand(IO $io): bool
    {
        $mutator = $io->getInput()->getArgument('Mutator name');

        if ($mutator === null) {
            $question = new Question('What mutator do you wish to create (e.g. `AnyStringToInfectedMutator`)?');
            $question->setValidator(static function (?string $answer): string {
                if ($answer === null || trim($answer) === '') {
                    throw new RuntimeException('Mutator name is mandaory.');
                }

                return $answer;
            });
            $mutator = $io->askQuestion(
                $question,
            );
        }

        $mutator = ucfirst((string) $mutator);

        // find all files in templates directory
        $finder = Finder::create()
            ->files()
            ->in(__DIR__ . '/../CustomMutator/templates');

        $currentDirectory = getcwd();

        $generatedFilePaths = [];

        $fileInfos = iterator_to_array($finder->getIterator());
        $fileSystem = $this->getApplication()->getContainer()->getFileSystem();

        foreach ($fileInfos as $fileInfo) {
            // replace __Name__ with $mutator
            $newContent = $this->replaceNameVariable($mutator, $fileInfo->getContents());
            $replacedNamePath = $this->replaceNameVariable($mutator, $fileInfo->getRelativePathname());

            $newFilePath = $currentDirectory . '/src/Mutator/' . $replacedNamePath;

            $fileSystem->dumpFile($newFilePath, $newContent);

            $generatedFilePaths[] = $newFilePath;
        }

        $io->title('Generated files');
        $io->listing($generatedFilePaths);
        $io->success(
            sprintf('Base classes for the "%s" mutator were created. Complee the missing parts inside them.', $mutator),
        );

        return true;
    }

    private function replaceNameVariable(string $rectorName, string $contents): string
    {
        return str_replace('__Name__', $rectorName, $contents);
    }
}
