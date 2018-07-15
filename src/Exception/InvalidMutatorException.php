<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Exception;

use Infection\Mutator\Util\Mutator;

/**
 * @internal
 */
final class InvalidMutatorException extends \Exception
{
    public static function create(string $filePath, Mutator $mutator, \Throwable $previous): self
    {
        return new self(
            sprintf(
                'Encountered an error with the "%s" mutator in the "%s" file. ' .
                'This is most likely a bug in Infection, so please report this in our issue tracker.',
                $mutator::getName(),
                $filePath
            ),
            0,
            $previous
        );
    }
}
