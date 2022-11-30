<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ReflectionVisitor;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class LineRangeCalculator
{
    public function calculateRange(Node $originalNode) : NodeLineRangeData
    {
        if ($originalNode->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, \false) === \true) {
            $startLine = $originalNode->getStartLine();
            return new NodeLineRangeData($startLine, $startLine);
        }
        $outerMostArrayNode = $this->getOuterMostArrayNode($originalNode);
        return new NodeLineRangeData($outerMostArrayNode->getStartLine(), $outerMostArrayNode->getEndLine());
    }
    private function getOuterMostArrayNode(Node $node) : Node
    {
        $outerMostArrayParent = $node;
        do {
            if ($node instanceof Node\Expr\Array_) {
                $outerMostArrayParent = $node;
            }
            $node = ParentConnector::findParent($node);
        } while ($node !== null);
        return $outerMostArrayParent;
    }
}
