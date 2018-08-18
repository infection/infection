<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutant\Exception;

/**
 * @internal
 */
final class ParserException extends \Exception
{
    public static function fromInvalidFile(\SplFileInfo $file, \Throwable $original): self
    {
        return new self(
            sprintf(
                'Unable to parse file "%s", most likely due to syntax errors.',
                $file->getRealPath()
            ),
            0,
            $original
        );
    }
}
