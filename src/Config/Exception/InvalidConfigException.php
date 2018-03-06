<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config\Exception;

final class InvalidConfigException extends \RuntimeException
{
    public static function invalidJson(string $configFile, string $errorMessage): self
    {
        return new self(sprintf(
            'The configuration file "%s" does not contain valid JSON: %s.',
            $configFile,
            $errorMessage
        ));
    }
}
