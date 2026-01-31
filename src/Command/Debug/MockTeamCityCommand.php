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

namespace Infection\Command\Debug;

use Closure;
use function explode;
use Infection\Command\BaseCommand;
use Infection\Command\Option\ConfigurationOption;
use Infection\Console\IO;
use const PHP_EOL;
use function Safe\stream_get_contents;
use function sprintf;
use const STDIN;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class MockTeamCityCommand extends BaseCommand
{
    private const MILLISECONDS_IN_MICROSECONDS = 1000;

    private const LOG_FILE_PATH_ARGUMENT = 'log';

    private const TIME_IN_MICRO_SECONDS_OPTION = 'time';

    private const DEFAULT_TIME_IN_MILLISECONDS = 500;  // 0.5s

    private readonly Closure $sleep;

    /**
     * @param (Closure(positive-int|0):void)|null $sleep
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        ?Closure $sleep = null,
    ) {
        // TODO: should be debug:mock-teamcity
        //  https://github.com/j-plugins/infection-plugin/issues/28
        parent::__construct('debug:mock-teamcity');
        // parent::__construct('run');

        $this->sleep = $sleep ?? usleep(...);
    }

    protected function configure(): void
    {
        $name = $this->getName();

        $this->setDescription(
            'Log the provided TeamCity log.',
        );
        $this->setHelp(
            <<<HELP
                This command will pick the provided log and output it as if it was the log it was emitting

                This is useful to debug or test how the Infection plugin behaves with a given log. For
                making it a bit more realistic, some timings are applied in-between each log.

                Examples:

                ```shell
                infection $name teamcity.log
                # or
                cat teamcity.log | infection $name
                # or
                infection $name < teamcity.log
                ```
                HELP,
        );

        $this->addArgument(
            self::LOG_FILE_PATH_ARGUMENT,
            InputArgument::OPTIONAL,
            'The content of the TeamCity log.',
        );
        $this->addOption(
            self::TIME_IN_MICRO_SECONDS_OPTION,
            null,
            InputOption::VALUE_REQUIRED,
            'Time to wait in-between each log, in milliseconds (ms).',
            self::DEFAULT_TIME_IN_MILLISECONDS,
        );
        // This is not used; this is purely to allow the function to be executed by the plugin which
        // always appends the configuration.
        ConfigurationOption::addOption($this);
    }

    protected function executeCommand(IO $io): bool
    {
        $logLines = $this->getLogLines($io);
        $timeInMicroSeconds = self::getTimeInMilliseconds($io);

        foreach ($logLines as $logLine) {
            $io->writeln($logLine);

            ($this->sleep)($timeInMicroSeconds);
        }

        return true;
    }

    /**
     * @return iterable<string>
     */
    private function getLogLines(IO $io): iterable
    {
        $path = $this->getLogFile($io);

        yield from $path === null
            ? $this->getLinesFromStdin($io->getInput())
            : explode(
                PHP_EOL,
                $this->filesystem->readFile($path),
            );
    }

    /**
     * @return iterable<string>
     */
    private function getLinesFromStdin(InputInterface $input): iterable
    {
        $inputStream = $input instanceof StreamableInputInterface
            ? $input->getStream()
            : null;
        $inputStream ??= STDIN;

        return explode(
            PHP_EOL,
            stream_get_contents($inputStream),
        );
    }

    /**
     * @return non-empty-string|null
     */
    private function getLogFile(IO $io): ?string
    {
        $path = trim((string) $io->getInput()->getArgument(self::LOG_FILE_PATH_ARGUMENT));

        $canonicalPath = $path === ''
            ? null
            : Path::canonicalize($path);

        Assert::nullOrStringNotEmpty($canonicalPath);

        return $canonicalPath;
    }

    /**
     * @return positive-int|0
     */
    private static function getTimeInMilliseconds(IO $io): int
    {
        $value = $io->getInput()->getOption(self::TIME_IN_MICRO_SECONDS_OPTION);

        Assert::integerish(
            $value,
            sprintf(
                'Expected a natural value for the option "--%s". Got "%s".',
                self::TIME_IN_MICRO_SECONDS_OPTION,
                $value,
            ),
        );

        $intValue = (int) $value;

        Assert::natural(
            $intValue,
            sprintf(
                'Expected a natural value for the option "--%s". Got "%s".',
                self::TIME_IN_MICRO_SECONDS_OPTION,
                $intValue,
            ),
        );

        return $intValue * self::MILLISECONDS_IN_MICROSECONDS;
    }
}
