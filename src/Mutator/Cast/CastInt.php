<?php

declare(strict_types=1);

namespace Infection\Mutator\Cast;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

class CastInt extends Mutator
{
    public function mutate(Node $node)
    {
        return $node->expr;
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\Cast\Int_;
    }
}