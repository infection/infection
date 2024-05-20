<?php

declare(strict_types=1);

namespace Infection\Testing;

use Infection\Mutator\Mutator;
use Infection\PhpParser\MutatedNode;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class SimpleMutationsCollectorVisitor extends NodeVisitorAbstract
{
    /**
     * @var SimpleMutation[]
     */
    private $mutations = [];

    /**
     * @param Mutator<Node> $mutator
     */
    public function __construct(
        private readonly Mutator $mutator,
        /**
         * @var Node[]
         */
        private readonly array $fileAst
    )
    {
    }

    public function leaveNode(Node $node)
    {
        if (!$this->mutator->canMutate($node)) {
            return;
        }

        // It is important to not rely on the keys here. It might otherwise result in some elements
        // being overridden, see https://3v4l.org/JLN73
        foreach ($this->mutator->mutate($node) as $mutatedNode) {
            $this->mutations[] = new SimpleMutation(
                $this->fileAst,
                $this->mutator,
                MutatedNode::wrap($mutatedNode),
                $node->getAttributes(),
                $node::class
            );
        }
    }

    /**
     * @return SimpleMutation[]
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }
}
