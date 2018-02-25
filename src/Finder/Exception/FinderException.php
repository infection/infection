<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Finder\Exception;

class FinderException extends \RuntimeException
{
    public static function composerNotFound(): self
    {
        return new self(
            'Unable to locate a Composer executable on local system. Ensure that Composer is installed and available.'
        );
    }

    public static function testFrameworkNotFound(string $testFrameworkName): self
    {
        return new self(
            sprintf(
                'Unable to locate a %s executable on local system. Ensure that %s is installed and available.',
                $testFrameworkName,
                $testFrameworkName
            )
        );
    }
}
