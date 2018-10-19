<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Unwrap;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
abstract class AbstractUnwrapMutator extends Mutator
{
    abstract protected function getFunctionName(): string;

    abstract protected function getParameterIndex(): int;

    /**
     * Replaces "$a = function(arg1, arg2);" with "$a = arg1;"
     *
     * @return Node\Param;
     */
    final public function mutate(Node $node)
    {
        return $node->args[$this->getParameterIndex()];
    }

    final protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return false;
        }

        return $node->name->toLowerString() === strtolower($this->getFunctionName());
    }
}
