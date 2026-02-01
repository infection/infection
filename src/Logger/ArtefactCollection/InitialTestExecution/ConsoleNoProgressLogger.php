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

namespace Infection\Logger\ArtefactCollection\InitialTestExecution;

use Infection\AbstractTestFramework\InvalidVersion;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use InvalidArgumentException;
use function sprintf;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final readonly class ConsoleNoProgressLogger implements InitialTestExecutionLogger
{
    private ProgressBar $progressBar;

    public function __construct(
        private OutputInterface $output,
        private TestFrameworkAdapter|StaticAnalysisToolAdapter $testFramework,
    ) {
        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat('verbose');
    }

    public function start(): void
    {
        try {
            $version = $this->testFramework->getVersion();
        } catch (InvalidVersion|InvalidArgumentException) {
            $version = 'unknown';
        }

        $this->output->writeln([
            '',
            sprintf(
                'Initial execution of %s version: %s',
                $this->testFramework->getName(),
                $version,
            ),
            '',
        ]);
    }

    public function advance(): void
    {
    }

    public function finish(string $executionOutput): void
    {
        // TODO: currently we do not log anything... But I don't think that's good.
        //   for example we could at least log metrics... or still if debug mode is enabled.
    }
}
