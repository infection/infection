<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Regex;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class PregMatchMatches extends Mutator
{
    /**
     * Replaces "preg_match('/a/', 'b', $foo);" with "(int) $foo = array();"
     *
     * @param Node|Node\Expr\FuncCall $node
     *
     * @return Node\Expr\Cast\Int_
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\Cast\Int_(new Node\Expr\Assign($node->args[2]->value, new Node\Expr\Array_()));
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return false;
        }

        if (!$node->name instanceof Node\Name ||
            $node->name->toLowerString() !== 'preg_match') {
            return false;
        }

        return \count($node->args) >= 3;
    }
}
