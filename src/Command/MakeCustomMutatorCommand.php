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

use function basename;
use Infection\Console\IO;
use RuntimeException;
use function Safe\file_get_contents;
use function Safe\getcwd;
use function sprintf;
use function str_replace;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use function trim;
use function ucfirst;

/**
 * @internal
 */
final class MakeCustomMutatorCommand extends BaseCommand
{
    private const MUTATOR_NAME_ARGUMENT = 'Mutator name';

    protected function configure(): void
    {
        $this
            ->setName('make:mutator')
            ->setDescription('Creates a custom mutator')
            ->addArgument(self::MUTATOR_NAME_ARGUMENT, InputArgument::REQUIRED);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $mutatorName = $input->getArgument(self::MUTATOR_NAME_ARGUMENT);

        if ($this->mutatorNameIsEmpty($mutatorName)) {
            $mutatorName = $this->askMutatorName();

            $input->setArgument(self::MUTATOR_NAME_ARGUMENT, $mutatorName);
        }
    }

    protected function executeCommand(IO $io): bool
    {
        $mutatorName = ucfirst(trim((string) $io->getInput()->getArgument(self::MUTATOR_NAME_ARGUMENT)));

        $templateFilePaths = [
            __DIR__ . '/../CustomMutator/templates/__Name__.php',
            __DIR__ . '/../CustomMutator/templates/__Name__Test.php',
        ];

        $generatedFilePaths = $this->createProjectFilesFromTemplates($templateFilePaths, $mutatorName);

        $io->title('Generated files');
        $io->listing($generatedFilePaths);
        $io->success(
            sprintf('Base classes for the mutator "%s" were created. Complete the missing parts inside them.', $mutatorName),
        );

        return true;
    }

    private static function replaceNameVariable(string $rectorName, string $contents): string
    {
        return str_replace('__Name__', $rectorName, $contents);
    }

    private function askMutatorName(): mixed
    {
        $question = new Question('What mutator do you wish to create (e.g. `AnyStringToInfectedMutator`)?');

        $question->setValidator(function (?string $answer): string {
            if ($this->mutatorNameIsEmpty($answer)) {
                throw new RuntimeException('Mutator name is mandatory.');
            }

            return $answer;
        });

        return $this->io->askQuestion(
            $question,
        );
    }

    private function mutatorNameIsEmpty(?string $mutatorName): bool
    {
        return $mutatorName === null || trim($mutatorName) === '';
    }

    /**
     * @param list<string> $filePaths
     * @return list<string>
     */
    private function createProjectFilesFromTemplates(array $filePaths, string $mutatorName): array
    {
        $currentDirectory = getcwd();
        $generatedFilePaths = [];

        $fileSystem = $this->getApplication()->getContainer()->getFileSystem();

        foreach ($filePaths as $filePath) {
            // replace __Name__ with $mutatorName
            $newContent = self::replaceNameVariable($mutatorName, file_get_contents($filePath));
            $replacedNamePath = self::replaceNameVariable($mutatorName, basename($filePath));

            $newFilePath = $currentDirectory . '/src/Mutator/' . $replacedNamePath;

            $fileSystem->dumpFile($newFilePath, $newContent);

            $generatedFilePaths[] = $newFilePath;
        }

        return $generatedFilePaths;
    }
}
