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
        if ($logFilePath === null) {
            return;
        }
        $total = $this->metricsCalculator->getTotalMutantsCount();
        $killed = $this->metricsCalculator->getKilledCount();
        $errored = $this->metricsCalculator->getErrorCount();
        $escaped = $this->metricsCalculator->getEscapedCount();
        $timedOut = $this->metricsCalculator->getTimedOutCount();
        $notCovered = $this->metricsCalculator->getNotCoveredByTestsCount();
        $this->fs->dumpFile(
            $logFilePath,
            implode(
                [
                    'Total: ' . $total,
                    'Killed: ' . $killed,
                    'Errored: ' . $errored,
                    'Escaped: ' . $escaped,
                    'Timed Out: ' . $timedOut,
                    'Not Covered: ' . $notCovered,
                ],
                "\n"
            )
        );
    }
}
