<?php

declare(strict_types=1);

namespace newSrc\AST\NodeVisitor;

use newSrc\AST\Annotation;
use newSrc\AST\AridCodeDetector;
use newSrc\AST\NodeStateTracker;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

// All files being traversed are covered by tests, but not all the code of that file is covered by tests.
final class LabelAridCodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private NodeStateTracker $nodeStateTracker,
        private AridCodeDetector $aridCodeDetector,
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
}
