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

namespace Infection\Event\Subscriber;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Event\InitialTestCaseWasCompleted;
use Infection\Event\InitialTestSuiteWasFinished;
use Infection\Event\InitialTestSuiteWasStarted;
use InvalidArgumentException;
use const PHP_EOL;
use function Safe\sprintf;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class InitialTestsConsoleLoggerSubscriber implements EventSubscriber
{
    private OutputInterface $output;
    private ProgressBar $progressBar;
    private TestFrameworkAdapter $testFrameworkAdapter;
    private bool $debug;

    public function __construct(OutputInterface $output, TestFrameworkAdapter $testFrameworkAdapter, bool $debug)
    {
        $this->output = $output;
        $this->testFrameworkAdapter = $testFrameworkAdapter;
        $this->debug = $debug;

        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat('verbose');
    }

    public function onInitialTestSuiteWasStarted(InitialTestSuiteWasStarted $event): void
    {
        try {
            $version = $this->testFrameworkAdapter->getVersion();
        } catch (InvalidArgumentException $e) {
            $version = 'unknown';
        }

        $this->output->writeln([
            '',
            'Running initial test suite...',
            '',
            sprintf(
                '%s version: %s',
                $this->testFrameworkAdapter->getName(),
                $version
            ),
            '',
        ]);
        $this->progressBar->start();
    }

    public function onInitialTestSuiteWasFinished(InitialTestSuiteWasFinished $event): void
    {
        $this->progressBar->finish();

        if ($this->debug) {
            $this->output->writeln(PHP_EOL . $event->getOutputText());
        }
    }

    public function onInitialTestCaseWasCompleted(InitialTestCaseWasCompleted $event): void
    {
        $this->progressBar->advance();
    }
}
