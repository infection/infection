<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Unwrap;

/**
 * @internal
 */
final class UnwrapArrayFilter extends AbstractUnwrapMutator
{
    protected function getFunctionName(): string
    {
        return 'array_filter';
    }

    protected function getParameterIndex(): int
    {
        return 0;
    }
}
