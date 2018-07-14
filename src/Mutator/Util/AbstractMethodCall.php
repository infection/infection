<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Util;

use PhpParser\Node;

abstract class AbstractMethodCall extends Mutator
{
    public function mutate(Node $node)
    {
        return new Node\Expr\ConstFetch(new Node\Name(static::REPLACEMENT));
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\FuncCall;
    }
}
