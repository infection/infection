<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config;

use Infection\Logger\ResultsLoggerTypes;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @internal
 */
final class Validator
{
    public function validate(InfectionConfig $infectionConfig): bool
    {
        $this->validateLogFilePaths($infectionConfig);

        return true;
    }

    private function validateLogFilePaths(InfectionConfig $config): void
    {
        $logTypes = $config->getLogsTypes();

        foreach ($logTypes as $logType => $file) {
            if ($logType !== ResultsLoggerTypes::BADGE) {
                $dir = \dirname($file);

                if (is_dir($dir) && !is_writable($dir)) {
                    throw new IOException(
                        sprintf('Unable to write to the "%s" directory. Check "logs.%s" file path in infection.json.', $dir, $logType),
                        0,
                        null,
                        $dir
                    );
                }
            }
        }
    }
}
