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
final class FunctionCallRemoval extends Mutator
{
    /**
     * Replaces "doSmth()" with ""
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

        if (!$node->expr instanceof Node\Expr\FuncCall) {
            return false;
        }

        $name = $node->expr->name;

        if (!$name instanceof Node\Name) {
            return true;
        }

        $string = strtolower((string) $name);

        if ($string === 'assert') {
            return false;
        }

        return true;
    }
}
