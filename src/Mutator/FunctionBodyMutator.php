<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator;

abstract class FunctionBodyMutator extends Mutator
{
    public function isFunctionBodyMutator(): bool
    {
        return true;
    }

    public function isFunctionSignatureMutator(): bool
    {
        return false;
    }
}
