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

namespace Infection\Logger;

use function in_array;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\MutantProcess;
use function Safe\file_put_contents;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
abstract class FileLogger implements MutationTestingResultsLogger
{
    /**
     * @var MetricsCalculator
     */
    protected $metricsCalculator;

    /**
     * @var bool
     */
    protected $isDebugVerbosity;

    /**
     * @var bool
     */
    protected $isDebugMode;

    /**
     * @var bool
     */
    protected $isOnlyCoveredMode;
    private $logFilePath;
    private $fs;
    private $output;

    public function __construct(
        OutputInterface $output,
        string $logFilePath,
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        bool $isDebugVerbosity,
        bool $isDebugMode,
        bool $isOnlyCoveredMode = false
    ) {
        $this->logFilePath = $logFilePath;
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->isDebugVerbosity = $isDebugVerbosity;
        $this->isDebugMode = $isDebugMode;
        $this->output = $output;
        $this->isOnlyCoveredMode = $isOnlyCoveredMode;
    }

    public function log(): void
    {
        $content = implode(PHP_EOL, $this->getLogLines());

        // If the output should be written to a stream then just write it directly
        if (strpos($this->logFilePath, 'php://') === 0) {
            if (in_array($this->logFilePath, ['php://stdout', 'php://stderr'], true)) {
                file_put_contents($this->logFilePath, $content);
            } else {
                // The Symfony filesystem component doesn't support using streams so provide a sensible error message
                $this->output->writeln(sprintf('<error>%s</error>', 'The only streams supported are php://stdout and php://stderr'));
            }

            return;
        }

        try {
            $this->fs->dumpFile($this->logFilePath, $content);
        } catch (IOException $e) {
            $this->output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }

    /**
     * @return string[]
     */
    abstract protected function getLogLines(): array;

    /**
     * @param MutantProcess[] $processes
     */
    final protected function sortProcesses(array &$processes): void
    {
        usort($processes, static function (MutantProcess $a, MutantProcess $b): int {
            if ($a->getOriginalFilePath() === $b->getOriginalFilePath()) {
                return $a->getOriginalStartingLine() <=> $b->getOriginalStartingLine();
            }

            return $a->getOriginalFilePath() <=> $b->getOriginalFilePath();
        });
    }
}
