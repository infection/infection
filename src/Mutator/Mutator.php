<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator;

use PhpParser\Node;

abstract class Mutator
{
    abstract public function mutate(Node $node);

    abstract public function shouldMutate(Node $node): bool;

    abstract public function isFunctionBodyMutator(): bool;

    abstract public function isFunctionSignatureMutator(): bool;

    public function getName(): string
    {
        $parts = explode('\\', static::class);

        return $parts[count($parts) - 1];
    }
}
