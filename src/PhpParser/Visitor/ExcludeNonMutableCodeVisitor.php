<?php

declare(strict_types=1);

namespace Infection\PhpParser\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class ExcludeNonMutableCodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node): null
    {
        if (!$this->isOnFunctionSignature($node)
            && !$this->isInsideFunction($node)
        ) {
            LabelNodesAsEligibleVisitor::markAsIneligible($node);
        }

        return null;
    }

    private function isOnFunctionSignature(Node $node): bool
    {
        return $node->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);
    }

    private function isInsideFunction(Node $node): bool
    {
        return $node->getAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY, false);
    }
}
