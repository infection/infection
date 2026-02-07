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

namespace Infection\Reporter;

use Generator;
use function sprintf;
use function str_repeat;
use function str_starts_with;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Decorator that generates a list of generated report file locations to the console.
 * It does NOT execute the decorated report.
 *
 * @internal
 */
final readonly class FileLocationReporter implements Reporter
{
    private const PAD_LENGTH = 8;

    public function __construct(
        private Reporter $decoratedReporter,
        private OutputInterface $output,
        private ?int $numberOfShownMutations,
    ) {
    }

    public function report(): void
    {
        $hasReporters = false;

        foreach ($this->getFileReporters($this->decoratedReporter) as $fileReporter) {
            if (!$hasReporters) {
                $this->output->writeln(['', 'Generated Reports:']);
            }
            $this->output->writeln(
                $this->addIndentation(sprintf('- %s', $fileReporter->getFilePath())),
            );
            $hasReporters = true;
        }

        if ($hasReporters) {
            return;
        }

        // for the case when no file reporters are configured and `--show-mutations` is not used
        if ($this->numberOfShownMutations === 0) {
            $this->output->writeln(['', 'Note: to see escaped mutants run Infection with "--show-mutations=20" or configure file reporters.']);
        }
    }

    /**
     * @return Generator<FileReporter>
     */
    private function getFileReporters(Reporter ...$reporters): Generator
    {
        foreach ($reporters as $reporter) {
            if ($reporter instanceof FederatedReporter) {
                yield from $this->getFileReporters(...$reporter->reporters);
            } elseif ($reporter instanceof FileReporter && !str_starts_with($reporter->getFilePath(), 'php://')) {
                yield $reporter;
            }
        }
    }

    private function addIndentation(string $string): string
    {
        return str_repeat(' ', self::PAD_LENGTH + 1) . $string;
    }
}
