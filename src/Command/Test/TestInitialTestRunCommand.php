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

namespace Infection\Command\Test;

use Infection\Command\Test\Option\InitialTestsPhpOptionsOption;
use Infection\Command\Test\Option\MapSourceClassToTestOption;
use Infection\Command\Test\Option\TestFrameworkOption;
use Infection\Command\Test\Option\TestFrameworkOptionsOption;
use function explode;
use Infection\Command\BaseCommand;
use Infection\Command\Git\Option\BaseOption;
use Infection\Command\Git\Option\FilterOption;
use Infection\Command\Option\ConfigurationOption;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Console\ConsoleOutput;
use Infection\Console\IO;
use Infection\Console\LogVerbosity;
use Infection\Logger\ConsoleLogger;
use Infection\Process\Runner\InitialTestsFailed;

/**
 * @internal
 */
final class TestInitialTestRunCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('test:initial-test-run');
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Executes the initial test run as orchestrated by Infection.',
        );

        ConfigurationOption::addOption($this);
        BaseOption::addOption($this);
        FilterOption::addOption($this);
        InitialTestsPhpOptionsOption::addOption($this);
        TestFrameworkOption::addOption($this);
        TestFrameworkOptionsOption::addOption($this);
        MapSourceClassToTestOption::addOption($this);
    }

    protected function executeCommand(IO $io): bool
    {
        $logger = new ConsoleLogger($io);
        $consoleOutput = new ConsoleOutput($logger);

        $inputBase = BaseOption::get($io);
        $inputFilter = FilterOption::get($io);

        $container = $this->getApplication()->getContainer()->withValues(
            logger: $logger,
            output: $io->getOutput(),
            configFile: ConfigurationOption::get($io),
            sourceFilter: new IncompleteGitDiffFilter($inputFilter, $inputBase),
        );

        // LogVerbosity::convertVerbosityLevel($io->getInput(), $consoleOutput);
        $container->getSubscriberRegisterer()->registerSubscribers($io->getOutput());

        $configuration = $container->getConfiguration();

        $initialTestSuiteProcess = $container->getInitialTestsRunner()->run(
            $configuration->testFrameworkExtraOptions,
            explode(' ', (string) $configuration->initialTestsPhpOptions),
            $configuration->skipCoverage,
        );

        if (!$initialTestSuiteProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcessAndAdapter(
                $initialTestSuiteProcess,
                $container->getTestFrameworkAdapter(),
            );
        }

        $io->newLine();
        $io->success('Initial test run successfully executed.');

        return true;
    }
}
