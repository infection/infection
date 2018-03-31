<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

final class SummaryFileLogger extends FileLogger
{
    protected function getLogLines(): array
    {
        $logs = [];

        $logs[] = 'Total: ' . $this->metricsCalculator->getTotalMutantsCount();
        $logs[] = 'Killed: ' . $this->metricsCalculator->getKilledCount();
        $logs[] = 'Errored: ' . $this->metricsCalculator->getErrorCount();
        $logs[] = 'Escaped: ' . $this->metricsCalculator->getEscapedCount();
        $logs[] = 'Timed Out: ' . $this->metricsCalculator->getTimedOutCount();
        $logs[] = 'Not Covered: ' . $this->metricsCalculator->getNotCoveredByTestsCount();

        return $logs;
    }
}
