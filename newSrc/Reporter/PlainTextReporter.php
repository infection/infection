<?php

declare(strict_types=1);

namespace newSrc\Reporter;

use newSrc\Engine\Envelope;
use newSrc\MutationAnalyzer\MutantExecutionResult;

final class PlainTextReporter implements Reporter
{
    public function collect(MutantExecutionResult $result, Envelope $envelope): void
    {
        // TODO: Implement collect() method.
    }

    public function report(): void
    {
        // TODO: Implement report() method.
    }
}
