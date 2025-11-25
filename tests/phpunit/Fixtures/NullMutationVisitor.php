<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutator\Mutator;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class NullMutationVisitor extends NodeVisitorAbstract
{
    /**
     * @param Mutator<Node> $mutator
     */
    public function __construct(private readonly Mutator $mutator)
    {
    }

    /**
     * Runs the mutator, but does mutate the node
     */
    public function leaveNode(Node $node): void
    {
        $clonedNode = clone $node;
        if (!$this->mutator->canMutate($clonedNode)) {
            return;
        }
        $this->mutator->mutate($clonedNode);

    }
}
