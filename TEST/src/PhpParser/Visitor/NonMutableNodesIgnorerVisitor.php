<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeTraverser;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
final class NonMutableNodesIgnorerVisitor extends NodeVisitorAbstract
{
    public function __construct(private array $nodeIgnorers)
    {
    }
    public function enterNode(Node $node)
    {
        foreach ($this->nodeIgnorers as $nodeIgnorer) {
            if ($nodeIgnorer->ignores($node)) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
        }
        return null;
    }
}
