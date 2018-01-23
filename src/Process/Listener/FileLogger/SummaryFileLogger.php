<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Listener\FileLogger;

class SummaryFileLogger extends FileLogger
{
    public function writeToFile()
    {
        $logs = [];

        $logs[] = 'Total: ' . $this->metricsCalculator->getTotalMutantsCount();
        $logs[] = 'Killed: ' . $this->metricsCalculator->getKilledCount();
        $logs[] = 'Errored: ' . $this->metricsCalculator->getErrorCount();
        $logs[] = 'Escaped: ' . $this->metricsCalculator->getEscapedCount();
        $logs[] = 'Timed Out: ' . $this->metricsCalculator->getTimedOutCount();
        $logs[] = 'Not Covered: ' . $this->metricsCalculator->getNotCoveredByTestsCount();

        $this->write($logs);
    }
}
