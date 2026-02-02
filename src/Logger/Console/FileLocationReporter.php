<?php

declare(strict_types=1);

namespace Infection\Logger\Console;

use Generator;
use Infection\Logger\FederatedLogger;
use Infection\Logger\FileLogger;
use Infection\Logger\MutationTestingResultsLogger;
use Symfony\Component\Console\Output\OutputInterface;
use function Pipeline\take;
use function sprintf;
use function str_repeat;

final readonly class FileLocationReporter implements MutationTestingResultsLogger
{
    private const PAD_LENGTH = 8;

    public function __construct(
        private MutationTestingResultsLogger $decoratedReporter,
        // TODO: shownMutationCount is probably a better name
        private ?int $numberOfShownMutations,
        private OutputInterface $output,
    ) {
    }

    public function log(): void
    {
        $hasLoggers = false;

        foreach ($this->getFileReportPaths() as $reportPath) {
            if (!$hasLoggers) {
                $this->output->writeln(['', 'Generated Reports:']);
            }

            $this->output->writeln(
                $this->addIndentation(
                    sprintf(
                        '- %s',
                        $reportPath,
                    ),
                ),
            );

            $hasLoggers = true;
        }

        if ($hasLoggers) {
            return;
        }

        // TODO: this comment looks weird to me: the user might have reports with a stream...
        // For the case when no file loggers are configured and `--show-mutations` is not used
        if ($this->numberOfShownMutations === 0) {
            $this->output->writeln(['', 'Note: to see escaped mutants run Infection with "--show-mutations=20" or configure file loggers.']);
        }
    }

    private function addIndentation(string $string): string
    {
        return str_repeat(' ', self::PAD_LENGTH + 1) . $string;
    }

    /**
     * @return iterable<string>
     */
    private function getFileReportPaths(): iterable
    {
        yield from take(self::unwrapReporterDecorators($this->decoratedReporter))
            ->map($this->mapReporterToReportPath(...))
            ->filter()
            ->getIterator();
    }

    /**
     * @return iterable<MutationTestingResultsLogger>
     */
    private function unwrapReporterDecorators(MutationTestingResultsLogger $reporter): iterable
    {
        // TODO: would be better to expose an interface for this instead.
        if ($reporter instanceof FederatedLogger) {
            foreach ($reporter->loggers as $reporter) {
                yield self::unwrapReporterDecorators($reporter);
            }
        } else {
            yield $reporter;
        }

        foreach ($this->decoratedReporter as $item) {

        }

        foreach ($allLoggers as $logger) {
            if ($logger instanceof FederatedLogger) {
                yield from $this->getFileReportPaths($logger->loggers);
            } elseif ($logger instanceof FileLogger && !str_starts_with($logger->getFilePath(), 'php://')) {
                yield $logger;
            }
        }
    }

    private function filterFileReporters(MutationTestingResultsLogger $reporter): bool
    {
        return $reporter instanceof FileLogger;
    }

    private function mapReporterToReportPath(MutationTestingResultsLogger $reporter): ?string
    {
        if ($reporter instanceof FileLogger) {
            $reportPath = $reporter->getFilePath();

            if (!self::isStream($reportPath)) {
                return $reportPath;
            }
        }

        return null;
    }

    private static function isStream(string $reportPath): bool
    {
        return !str_starts_with($reportPath, 'php://');
    }
}