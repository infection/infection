<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutator\Mutator;
use Infection\PhpParser\MutatedNode;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class SimpleMutationsCollectorVisitor extends NodeVisitorAbstract
{
    private $mutator;
    private $fileAst;

    /**
     * @var SimpleMutation[]
     */
    private $mutations = [];

    /**
     * @param Mutator $mutator
     * @param Node[]   $fileAst
     */
    public function __construct(Mutator $mutator, array $fileAst)
    {
        $this->mutator = $mutator;
        $this->fileAst = $fileAst;
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
                $this->mutator->getName(),
                MutatedNode::wrap($mutatedNode),
                $node->getAttributes(),
                get_class($node)
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
