<?php

declare(strict_types=1);

namespace newSrc\AST\NodeVisitor;

use newSrc\AST\Annotation;
use newSrc\AST\NodeStateTracker;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

// Propagate the labels. For instance if the parent node is marked as uncovered by tests
final class PropagateNodeLabelVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private NodeStateTracker $nodeStateTracker,
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if ($this->aridCodeDetector->isArid($node)) {
            $this->nodeStateTracker->startLabelNodesAs(Annotation::ARID_CODE);
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        $this->nodeStateTracker->stopIgnoring();

        return null;
    }

    private function getSymbol(Node $node): ?string {
        // TODO
        return 'Foo::bar()';
    }

}
