<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Rounding;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class Round extends Mutator
{
    /**
     * Replaces "floor();" and "ceil();" with "round();"
     *
     * @param Node|Node\Name $node
     *
     * @return Node\Expr\Cast\Int_
     */
    public function mutate(Node $node)
    {
        $node->name->parts = ['round'];

        return $node;
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node->name instanceof Node\Name && (
            strtolower((string) $node->name) === 'floor' || 
            strtolower((string) $node->name) === 'ceil'
        );
    }
}
