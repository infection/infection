<?php

declare(strict_types=1);

namespace Infection\Visitor;

use Infection\Mutation;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class MutationsCollectorNodeVisitor extends NodeVisitorAbstract
{
    private $mutators = [];
    private $mutations = [];

    public function __construct(array $mutators)
    {
        $this->mutators = $mutators;
    }

    public function leaveNode(Node $node)
    {
        // TODO if we are in function/method? test it
        foreach ($this->mutators as $mutator) {
            if ($mutator->shouldMutate($node)) {
                $this->mutations[] = new Mutation(
                    $mutator,
                    $mutator->mutate($node),
                    $node->getAttributes()
                );
            }
        }
    }

    /**
     * @return array
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }
}