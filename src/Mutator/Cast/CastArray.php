<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Cast;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

class CastArray extends Mutator
{
    public function mutate(Node $node)
    {
        return $node->expr;
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\Cast\Array_;
    }
}
