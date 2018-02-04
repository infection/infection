<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Console\Exception;

class InvalidOptionException extends InfectionException
{
    public static function withMessage(string $message)
    {
        return new self($message);
    }
}
