<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use function array_key_exists;
use _HumbugBox9658796bb9f0\Infection\Mutation\Mutation;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
final class MutatorVisitor extends NodeVisitorAbstract
{
    public function __construct(private Mutation $mutation)
    {
    }
    public function leaveNode(Node $node)
    {
        $attributes = $node->getAttributes();
        if (!array_key_exists('startTokenPos', $attributes)) {
            return null;
        }
        $mutatedAttributes = $this->mutation->getAttributes();
        $samePosition = $attributes['startTokenPos'] === $mutatedAttributes['startTokenPos'] && $attributes['endTokenPos'] === $mutatedAttributes['endTokenPos'];
        if ($samePosition && $this->mutation->getMutatedNodeClass() === $node::class) {
            return $this->mutation->getMutatedNode()->unwrap();
        }
        return null;
    }
}
