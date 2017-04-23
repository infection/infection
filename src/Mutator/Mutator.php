<?php

declare(strict_types=1);

namespace Infection\Mutator;

use PhpParser\Node;

interface Mutator
{
    public function mutate(Node $node);

    public function shouldMutate(Node $node) : bool;
}
