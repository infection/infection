<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Visitor\WrappedFunctionInfoCollectorVisitor;
use PhpParser\Node;

abstract class AbstractValueToNullReturnValue extends Mutator
{
    protected function isNullReturnValueAllowed(Node $node): bool
    {
        /** @var \PhpParser\Node\Stmt\Function_ $functionScope */
        $functionScope = $node->getAttribute(WrappedFunctionInfoCollectorVisitor::FUNCTION_SCOPE_KEY);

        $returnType = $functionScope->getReturnType();

        // no return value specified
        if (null === $returnType) {
            return true;
        }

        // scalar typehint
        if (\is_string($returnType)) {
            return false;
        }

        // nullable typehint, e.g. "?int" or "?CustomClass"
        if ($returnType instanceof Node\NullableType) {
            return true;
        }

        return !$returnType instanceof Node\Name;
    }
}
