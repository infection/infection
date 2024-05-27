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

use function array_merge;
use function class_exists;
use Composer\InstalledVersions;
use Infection\Command\ConfigureCommand;
use Infection\Command\DescribeCommand;
use Infection\Command\MakeCustomMutatorCommand;
use Infection\Command\RunCommand;
use Infection\Container;
use OutOfBoundsException;
use function preg_quote;
use function Safe\preg_match;
use function sprintf;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function trim;

/**
 * @internal
 */
final class Application extends BaseApplication
{
    public const PACKAGE_NAME = 'infection/infection';

    private const NAME = 'Infection - PHP Mutation Testing Framework';

    private const LOGO = '
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/

<fg=blue>#StandWith</><fg=yellow>Ukraine</>

';

    public function __construct(private readonly Container $container)
    {
        parent::__construct(self::NAME, self::getPrettyVersion());
        $this->setDefaultCommand('run');
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getLongVersion(): string
    {
        return trim(sprintf(
            '<info>%s</info> version <comment>%s</comment>',
            $this->getName(),
            $this->getVersion(),
        ));
    }

    public function getHelp(): string
    {
        return self::LOGO . parent::getHelp();
    }

    protected function getDefaultCommands(): array
    {
        $commands = array_merge(
            parent::getDefaultCommands(),
            [
                new ConfigureCommand(),
                new RunCommand(),
                new DescribeCommand(),
                new MakeCustomMutatorCommand(),
            ],
        );

        return $commands;
    }

    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        parent::configureIO($input, $output);

        if ($this->getContainer()->getCiDetector()->isCiDetected()) {
            $input->setInteractive(false);
        }

        OutputFormatterStyleConfigurator::configure($output);
    }

    private static function getPrettyVersion(): string
    {
        // Pre 2.0 Composer runtime didn't have this class.
        // @codeCoverageIgnoreStart
        if (!class_exists(InstalledVersions::class)) {
            return 'unknown';
        }
        // @codeCoverageIgnoreEnd

        try {
            return (string) InstalledVersions::getPrettyVersion(self::PACKAGE_NAME);
            // @codeCoverageIgnoreStart
        } catch (OutOfBoundsException $e) {
            if (preg_match('#package .*' . preg_quote(self::PACKAGE_NAME, '#') . '.* not installed#i', $e->getMessage()) === 0) {
                throw $e;
            }

            // We have a bogus exception: how can Infection be not installed if we're here?
            return 'not-installed';
        }
        // @codeCoverageIgnoreEnd
    }
}
