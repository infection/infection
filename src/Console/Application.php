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

namespace Infection\Console;

use Composer\XdebugHandler\XdebugHandler;
use function extension_loaded;
use Infection\Command\ConfigureCommand;
use Infection\Command\InfectionCommand;
use Infection\Console\ConsoleOutput as InfectionConsoleOutput;
use Infection\Container;
use OutOfBoundsException;
use PackageVersions\Versions;
use const PHP_SAPI;
use function preg_quote;
use function Safe\preg_match;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
final class Application extends BaseApplication
{
    private const NAME = 'Infection - PHP Mutation Testing Framework';

    private const PACKAGE_NAME = 'infection/infection';

    private const INFECTION_PREFIX = 'INFECTION';

    private const LOGO = '
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/
';

    private $container;

    /**
     * @var InfectionConsoleOutput
     */
    private $consoleOutput;

    public function __construct(Container $container)
    {
        try {
            $version = Versions::getVersion(self::PACKAGE_NAME);
            // @codeCoverageIgnoreStart
        } catch (OutOfBoundsException $e) {
            if (preg_match('/package .*' . preg_quote(self::PACKAGE_NAME) . '.* not installed/', $e->getMessage()) === 0) {
                throw $e;
            }

            // We have a bogus exception: how can Infection be not installed if we're here?
            $version = 'not-installed';
        }
        // @codeCoverageIgnoreEnd

        parent::__construct(self::NAME, $version);

        $this->container = $container;
        $this->setDefaultCommand('run');
    }

    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        if ($input === null) {
            $input = new ArgvInput();
        }

        if ($output === null) {
            $output = new ConsoleOutput();
        }

        $this->consoleOutput = new InfectionConsoleOutput(new SymfonyStyle($input, $output));

        if ($input->hasParameterOption(['--quiet', '-q'], true)) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        $this->logRunningWithDebugger();

        if (!$this->isAutoExitEnabled()) {
            // When we're not in control of exit codes, that means it's the caller
            // responsibility to disable xdebug if it isn't needed. As of writing
            // that's only the case during E2E testing. Show a warning nevertheless.

            $this->consoleOutput->logNotInControlOfExitCodes();

            return parent::run($input, $output);
        }

        (new XdebugHandler(self::INFECTION_PREFIX, '--ansi'))
            ->setPersistent()
            ->check();

        return parent::run($input, $output);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getConsoleOutput(): InfectionConsoleOutput
    {
        return $this->consoleOutput;
    }

    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        $output->writeln([self::LOGO, $this->getLongVersion()]);

        return parent::doRunCommand($command, $input, $output);
    }

    protected function getDefaultCommands()
    {
        $commands = array_merge(parent::getDefaultCommands(), [
            new ConfigureCommand(),
            new InfectionCommand(),
        ]);

        return $commands;
    }

    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        parent::configureIO($input, $output);

        $output->getFormatter()->setStyle('with-error', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('uncovered', new OutputFormatterStyle('blue', null, ['bold']));
        $output->getFormatter()->setStyle('timeout', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle('escaped', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('killed', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('code', new OutputFormatterStyle('white'));

        $output->getFormatter()->setStyle('diff-add', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('diff-del', new OutputFormatterStyle('red'));

        $output->getFormatter()->setStyle('low', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('medium', new OutputFormatterStyle('yellow', null, ['bold']));
        $output->getFormatter()->setStyle('high', new OutputFormatterStyle('green', null, ['bold']));
    }

    private function logRunningWithDebugger(): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $this->consoleOutput->logRunningWithDebugger(PHP_SAPI);
        } elseif (extension_loaded('xdebug')) {
            $this->consoleOutput->logRunningWithDebugger('Xdebug');
        } elseif (extension_loaded('pcov')) {
            $this->consoleOutput->logRunningWithDebugger('PCOV');
        }
    }
}
