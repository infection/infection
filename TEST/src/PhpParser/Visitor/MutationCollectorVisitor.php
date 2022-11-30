<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use _HumbugBox9658796bb9f0\Infection\Mutation\Mutation;
use _HumbugBox9658796bb9f0\Infection\Mutator\NodeMutationGenerator;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
final class MutationCollectorVisitor extends NodeVisitorAbstract
{
    private array $mutationChunks = [];
    public function __construct(private NodeMutationGenerator $mutationGenerator)
    {
    }
    public function beforeTraverse(array $nodes) : ?array
    {
        $this->mutationChunks = [];
        return null;
    }
    public function leaveNode(Node $node) : ?Node
    {
        $this->mutationChunks[] = $this->mutationGenerator->generate($node);
        return null;
    }
    public function getMutations() : iterable
    {
        foreach ($this->mutationChunks as $mutations) {
            yield from $mutations;
        }
    }
}
