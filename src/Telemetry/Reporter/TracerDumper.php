<?php

declare(strict_types=1);

namespace Infection\Telemetry\Reporter;

use Infection\Telemetry\Tracing\Trace;
use Symfony\Component\Filesystem\Filesystem;
use function serialize;
use function sprintf;
use const DIRECTORY_SEPARATOR;

final readonly class TracerDumper implements TraceReporter, TraceProvider
{
    public function __construct(
        private Filesystem $filesystem,
        private string $dir,
    ) {
    }

    public function report(Trace $trace): void
    {
        $this->filesystem->dumpFile(
            $this->dir.DIRECTORY_SEPARATOR.self::createFileName($trace),
            serialize($trace),
        );
    }

    public function getTrace(): Trace
    {
        // TODO: Implement getTrace() method.
    }

    private static function createFileName(Trace $trace): string
    {
        return 'trace-'.$trace->id;
    }
}
