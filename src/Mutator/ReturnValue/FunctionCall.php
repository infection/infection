<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\ReturnValue;

use Infection\Mutator\FunctionBodyMutator;
use Infection\Visitor\WrappedFunctionInfoCollectorVisitor;
use PhpParser\Node;

class FunctionCall extends FunctionBodyMutator
{
    /**
     * Replaces "return func();" with "func(); return null;"
     *
     * @param Node $node
     * @return array
     */
    public function mutate(Node $node)
    {
        return [
            $node->expr,
            new Node\Stmt\Return_(
                new Node\Expr\ConstFetch(new Node\Name('null'))
            ),
        ];
    }

    public function shouldMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return false;
        }

        if (!$node->expr instanceof Node\Expr\FuncCall) {
            return false;
        }

        /** @var \PhpParser\Node\Stmt\Function_ $functionScope */
        $functionScope = $node->getAttribute(WrappedFunctionInfoCollectorVisitor::FUNCTION_SCOPE_KEY);

        $returnType = $functionScope->getReturnType();

        // no return value specified
        if (null === $returnType) {
            return true;
        }

        // scalar typehint
        if (is_string($returnType)) {
            return false;
        }

        // nullable typehint, e.g. "?int" or "?CustomClass"
        if ($returnType instanceof Node\NullableType) {
            return true;
        }

        return !$returnType instanceof Node\Name;
    }
}
