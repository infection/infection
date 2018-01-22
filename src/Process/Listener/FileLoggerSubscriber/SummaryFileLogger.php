<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Process\Listener\FileLoggerSubscriber;

class SummaryFileLogger extends FileLogger
{
    public function writeToFile()
    {
        $logFilePath = $this->infectionConfig->getLogPathInfoFor('summary');
        $logs = [];

        $logs[] = 'Total: ' . $this->metricsCalculator->getTotalMutantsCount();
        $logs[] = 'Killed: ' . $this->metricsCalculator->getKilledCount();
        $logs[] = 'Errored: ' . $this->metricsCalculator->getErrorCount();
        $logs[] = 'Escaped: ' . $this->metricsCalculator->getEscapedCount();
        $logs[] = 'Timed Out: ' . $this->metricsCalculator->getTimedOutCount();
        $logs[] = 'Not Covered: ' . $this->metricsCalculator->getNotCoveredByTestsCount();

        $this->write($logs, $logFilePath);
    }
}
