<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config\Exception;

/**
 * @internal
 */
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

    public static function invalidMutator(string $mutator): self
    {
        return new self(sprintf(
           'The "%s" mutator/profile was not recognized.',
           $mutator
        ));
    }

    public static function invalidProfile(string $profile, string $mutator): self
    {
        return new self(sprintf(
            'The "%s" profile contains the "%s" mutator which was not recognized.',
            $profile,
            $mutator
        ));
    }
}
