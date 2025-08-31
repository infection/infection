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
use Infection\Telemetry\Tracing\RootScopes;
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
use Webmozart\Assert\Assert;
use function array_map;
use function extension_loaded;
use function implode;
use function sprintf;
use function trim;
use const PHP_INT_MAX;
use const PHP_SAPI;

/**
 * @internal
 */
final class ShowTraceCommand extends BaseCommand
{
    private const TRACE_PATHNAME_ARGUMENT = 'trace';
    private const FORMAT_OPTION = 'format';
    private const MAX_DEPTH_OPTION = 'max-depth';
    private const TOP_SCOPES_OPTION = 'root-scopes';

    private const NO_MAX_DEPTH = 'all';

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
            ->addOption(
                self::MAX_DEPTH_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'The max depth displayed (int<1, max>|\'%s\'). Defaults to 1.',
                    self::NO_MAX_DEPTH,
                ),
                1,
            )
            ->addOption(
                self::TOP_SCOPES_OPTION,
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                sprintf(
                    'Scopes allowed. Default to "file" which is the most pertinent. Beware that a span may appear in multiple root scopes, which will distort the metrics. Allowed values: %s.',
                    RootScopes::getQuotedListOfValues(),
                ),
                [RootScopes::FILE->value],
            )
        ;
    }

    protected function executeCommand(IO $io): bool
    {
        $tracePathname = self::getPathname($io);
        $format = self::getFormat($io);
        $maxDepth = self::getMaxDepth($io);
        $rootScopes = self::getRootScopes($io);

        $trace = Trace::unserialize(
            $this->filesystem->readFile($tracePathname),
        );
        $reporter = $this->getConsoleReporter($io);

        $reporter->report(
            $trace,
            $maxDepth,
            $rootScopes,
        );

        return true;
    }

    private static function getPathname(IO $io): string
    {
        return Path::canonicalize(
            $io->getInput()->getArgument(self::TRACE_PATHNAME_ARGUMENT),
        );
    }

    private static function getFormat(IO $io): TraceFormat
    {
        return TraceFormat::tryFrom(
            $io->getInput()->getOption(self::FORMAT_OPTION),
        );
    }

    /**
     * @return positive-int
     */
    private static function getMaxDepth(IO $io): int
    {
        $value = $io->getInput()->getOption(self::MAX_DEPTH_OPTION);

        if (self::NO_MAX_DEPTH === $value) {
            return PHP_INT_MAX;
        }

        $integerValue = (int) $value;

        Assert::integerish($value);
        Assert::positiveInteger($integerValue);

        return $integerValue;
    }

    /**
     * @return list<RootScopes>
     */
    private static function getRootScopes(IO $io): array
    {
        return array_map(
            static fn (string $value) => RootScopes::from($value),
            $io->getInput()->getOption(self::TOP_SCOPES_OPTION),
        );
    }

    private function getConsoleReporter(IO $io): ConsoleReporter
    {
        $container = $this->getApplication()->getContainer();

        return new ConsoleReporter(
            $container->get(DurationFormatter::class),
            $container->get(MemoryFormatter::class),
            $io,
        );
    }
}
