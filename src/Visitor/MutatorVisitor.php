<?php

declare(strict_types=1);

namespace Infection\Visitor;

use Infection\Mutation;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class MutatorVisitor extends NodeVisitorAbstract
{
    private $mutation;

    public function __construct(Mutation $mutation)
    {
        $this->mutation = $mutation;
    }

    public function leaveNode(Node $node)
    {
        $mutator = $this->mutation->getMutator();
        $mutatedAttributes = $this->mutation->getAttributes();

        $attributes = $node->getAttributes();
        $isEqualPosition = $attributes['startTokenPos'] === $mutatedAttributes['startTokenPos'] &&
            $attributes['endTokenPos'] === $mutatedAttributes['endTokenPos'];

        if ($isEqualPosition && $mutator->shouldMutate($node)) {
            return $mutator->mutate($node); // TODO move ->mutate() here from collector?
            // TODO STOP TRAVERSING
            // TODO check all built-in visitors, in particular FirstFindingVisitor
            // TODO beforeTraverse - FirstFindingVisitor
            // TODO enterNode instead of leaveNode for '<' mutation to not travers children?
        }
    }
}
