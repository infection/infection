<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Util;

use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;

/**
 * @internal
 */
abstract class AbstractValueToNullReturnValue extends Mutator
{
    protected function isNullReturnValueAllowed(Node $node): bool
    {
        /** @var \PhpParser\Node\Stmt\Function_ $functionScope */
        $functionScope = $node->getAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY);

        if (!$functionScope) {
            return true;
        }

        $returnType = $functionScope->getReturnType();

        if ($returnType instanceof Node\Identifier) {
            $returnType = $returnType->name;
        }

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
