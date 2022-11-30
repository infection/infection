<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
final class SummaryFileLogger implements LineMutationTestingResultsLogger
{
    public function __construct(private MetricsCalculator $metricsCalculator)
    {
    }
    public function getLogLines() : array
    {
        return ['Total: ' . $this->metricsCalculator->getTotalMutantsCount(), '', 'Killed: ' . $this->metricsCalculator->getKilledCount(), 'Errored: ' . $this->metricsCalculator->getErrorCount(), 'Syntax Errors: ' . $this->metricsCalculator->getSyntaxErrorCount(), 'Escaped: ' . $this->metricsCalculator->getEscapedCount(), 'Timed Out: ' . $this->metricsCalculator->getTimedOutCount(), 'Skipped: ' . $this->metricsCalculator->getSkippedCount(), 'Ignored: ' . $this->metricsCalculator->getIgnoredCount(), 'Not Covered: ' . $this->metricsCalculator->getNotTestedCount(), ''];
    }
}
