<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

/**
 * @internal
 */
final class SummaryFileLogger extends FileLogger
{
    protected function getLogLines(): array
    {
        return [
            'Total: ' . $this->metricsCalculator->getTotalMutantsCount(),
            'Killed: ' . $this->metricsCalculator->getKilledCount(),
            'Errored: ' . $this->metricsCalculator->getErrorCount(),
            'Escaped: ' . $this->metricsCalculator->getEscapedCount(),
            'Timed Out: ' . $this->metricsCalculator->getTimedOutCount(),
            'Not Covered: ' . $this->metricsCalculator->getNotCoveredByTestsCount(),
        ];
    }
}
