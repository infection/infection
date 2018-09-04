<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Removal;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class MethodCallRemoval extends Mutator
{
    /**
     * Replaces "$object->doSmth()" with ""
     *
     * @param Node $node
     *
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Stmt\Nop();
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Expression) {
            return false;
        }

        return $node->expr instanceof Node\Expr\MethodCall || $node->expr instanceof Node\Expr\StaticCall;
    }
}
