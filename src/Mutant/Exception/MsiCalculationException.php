<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutant\Exception;

class MsiCalculationException extends \LogicException
{
    public static function create(string $type): MsiCalculationException
    {
        return new self(sprintf(
            'Seems like something is wrong with calculations and %s options.', $type
        ));
    }
}
