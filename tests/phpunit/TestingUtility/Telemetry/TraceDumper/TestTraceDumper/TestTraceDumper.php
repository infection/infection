<?php

declare(strict_types=1);

namespace Infection\Tests\TestingUtility\Telemetry\TraceDumper\TestTraceDumper;

use Infection\Telemetry\Tracing\Span;
use Infection\Telemetry\Tracing\Trace;
use Infection\Tests\TestingUtility\TreeFormatter\TreeFormatter;
use Infection\Tests\TestingUtility\TreeFormatter\UnicodeTreeDiagramDrawer\UnicodeTreeDiagramDrawer;
use function implode;
use function Pipeline\take;

/**
 * Service to dump a telemetry Trace as an ASCII tree, displaying only the span
 * IDs. This is meant for tests where the actual values held by the spans do not
 * matter.
 */
final readonly class TestTraceDumper
{
    private TreeFormatter $formatter;

    public function __construct() {
        $this->formatter = new TreeFormatter(
            new UnicodeTreeDiagramDrawer(),
            static fn (Span $span) => (string) $span->id,
            static fn (Span $span) => $span->children,
        );
    }

    public function dump(Trace $trace): string
    {
        return implode(
            "\n",
            take($this->formatter->render($trace->spans))->toList(),
        );
    }
}