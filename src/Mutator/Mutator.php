<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator;

use PhpParser\Node;

interface Mutator
{
    public function mutate(Node $node);

    public function shouldMutate(Node $node): bool;

    public function isFunctionBodyMutator(): bool;

    public function isFunctionSignatureMutator(): bool;
}
