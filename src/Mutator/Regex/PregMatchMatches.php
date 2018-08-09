<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Regex;

use Infection\Mutator\Util\Mutator;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;

/**
 * @internal
 */
final class PregMatchMatches extends Mutator
{
    /**
     * Replaces "preg_match('/a/', 'b', $foo);" with "$foo = array();"
     *
     * @param Node|Node\Expr\FuncCall $node
     *
     * @return Node\Expr\Assign
     */
    public function mutate(Node $node)
    {
        return new Node\Expr\Assign($node->args[2]->value, new Node\Expr\Array_());
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return false;
        }

        if (!$node->name instanceof Node\Name ||
            strtolower((string) $node->name) !== 'preg_match') {
            return false;
        }

        return \count($node->args) >= 3 && $this->isAllowedByReturnType($node);
    }

    private function isAllowedByReturnType(Node $node): bool
    {
        if (!(($parent = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY)) instanceof Node\Stmt\Return_)) {
            return true;
        }

        $functionScope = $parent->getAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY);
        $returnType = $functionScope->getReturnType();

        if ($returnType instanceof Node\Identifier) {
            $returnType = $returnType->name;
        }

        return null === $returnType;
    }
}
