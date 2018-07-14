<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Statement;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

class Assign extends Mutator
{
    public function mutate(Node $node)
    {
        return new Node\Expr\BinaryOp\BooleanOr(new Node\Expr\ConstFetch(new Node\Name('true')), $node, $node->getAttributes());
    }

    public function shouldMutate(Node $node): bool
    {
        // Expr_Assign -> (Variable | PropertyFetch) + (MethodCall | Variable | PropertyFetch)
        return $node instanceof Node\Expr\Assign &&
            ($node->var instanceof  Node\Expr\Variable || $node->var instanceof  Node\Expr\PropertyFetch) &&
            ($node->expr instanceof Node\Expr\MethodCall || $node->expr instanceof Node\Expr\FuncCall ||
                $node->expr instanceof Node\Expr\Variable || $node->expr instanceof Node\Expr\PropertyFetch);
    }
}
