<?php

declare(strict_types=1);

namespace newSrc\AST\NodeVisitor;

use newSrc\AST\NodeStateTracker;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

// Add types based on either stubs or PHPStan or other.
final class AddTypesVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private NodeStateTracker $nodeStateTracker,
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if ($this->nodeStateTracker->isEligible($node)) {
            // I would add those types in a way that it is lazily evaluated, i.e. we do not try to get the types for the node unless necessary.
        }

        return null;
    }
}
