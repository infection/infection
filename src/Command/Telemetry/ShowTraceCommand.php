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

namespace Infection\Command\Telemetry;

use Infection\Command\BaseCommand;
use Infection\Command\RunCommandHelper;
use Infection\Configuration\Schema\SchemaConfigurationLoader;
use Infection\Console\ConsoleOutput;
use Infection\Console\Input\MsiParser;
use Infection\Console\IO;
use Infection\Console\LogVerbosity;
use Infection\Console\OutputFormatter\FormatterName;
use Infection\Console\XdebugHandler;
use Infection\Container;
use Infection\Engine;
use Infection\Event\ApplicationExecutionWasStarted;
use Infection\FileSystem\Locator\FileNotFound;
use Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use Infection\FileSystem\Locator\Locator;
use Infection\Logger\ConsoleLogger;
use Infection\Logger\GitHub\NoFilesInDiffToMutate;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\TimeFormatter;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\Telemetry\Metric\Time\DurationFormatter;
use Infection\Telemetry\Reporter\ConsoleReporter;
use Infection\Telemetry\Reporter\TracerDumper;
use Infection\Telemetry\Reporter\TraceReporter;
use Infection\Telemetry\Tracing\Trace;
use Infection\TestFramework\Coverage\XmlReport\NoLineExecutedInDiffLinesMode;
use Infection\TestFramework\TestFrameworkTypes;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use UnitEnum;
use function array_map;
use function extension_loaded;
use function implode;
use function sprintf;
use function trim;
use const PHP_SAPI;

/**
 * @internal
 */
final class ShowTraceCommand extends BaseCommand
{
    private const TRACE_PATHNAME_ARGUMENT = 'trace';
    private const FORMAT_OPTION = 'format';

    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telemetry:trace:show')
            ->setDescription('Shows a trace')
            ->addArgument(
                self::TRACE_PATHNAME_ARGUMENT,
                InputArgument::REQUIRED,
                'Pathname to the trace file.',
            )
            ->addOption(
                self::FORMAT_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Format in which to display the dumped trace. One of: "%s"',
                    implode(
                        '", "',
                        array_map(
                            static fn (UnitEnum $enum) => $enum->value,
                            TraceFormat::cases(),
                        ),
                    ),
                ),
                TraceFormat::TEXT->value,
            )
        ;
    }

    protected function executeCommand(IO $io): bool
    {
        $tracePathname = self::getPathname($io->getInput());
        $format = self::getFormat($io->getInput());

        $trace = Trace::unserialize(
            $this->filesystem->readFile($tracePathname),
        );
        $reporter = $this->getReporter($format, $io);

        $reporter->report($trace);

        return true;
    }

    private static function getPathname(InputInterface $input): string
    {
        return Path::canonicalize(
            $input->getArgument(self::TRACE_PATHNAME_ARGUMENT),
        );
    }

    private static function getFormat(InputInterface $input): TraceFormat
    {
        return TraceFormat::tryFrom(
            $input->getOption(self::FORMAT_OPTION),
        );
    }

    private function getReporter(TraceFormat $format, IO $io): TraceReporter
    {
        $container = $this->getApplication()->getContainer();

        return match ($format) {
            default => new ConsoleReporter(
                $container->get(DurationFormatter::class),
                $container->get(MemoryFormatter::class),
                $io,
            ),
        };
    }
}
