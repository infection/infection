<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console\Exception;

class InfectionException extends \Exception
{
    public static function configurationAborted()
    {
        return new self('Configuration aborted');
    }
}
