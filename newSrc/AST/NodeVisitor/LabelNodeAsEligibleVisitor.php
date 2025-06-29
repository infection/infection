<?php

declare(strict_types=1);

namespace newSrc\AST\NodeVisitor;

use newSrc\AST\Annotation;
use newSrc\AST\NodeAnnotator;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

// Mark all node as eligible. This visitor should be registered as last, so if
// a node is code that should be ignored because not covered by tests, for example,
// then this visitor should not traverse that node at all.
final class LabelNodeAsEligibleVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node): int|null
    {
        NodeAnnotator::annotate($node, Annotation::ELIGIBLE);

        return null;
    }
}
