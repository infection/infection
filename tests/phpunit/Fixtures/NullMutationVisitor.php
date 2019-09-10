<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class NullMutationVisitor extends NodeVisitorAbstract
{
    /**
     * @var Mutator[]
     */
    private $mutator;

    public function __construct(Mutator $mutator)
    {
        $this->mutator = $mutator;
    }

    /**
     * Runs the mutator, but does mutate the node
     */
    public function leaveNode(Node $node)
    {
        $clonedNode = clone $node;
        if (!$this->mutator->shouldMutate($clonedNode)) {
            return;
        }
        $this->mutator->mutate($clonedNode);

    }
}
