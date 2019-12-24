<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Mutation;

use LogicException;
use PhpParser\Node;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;

final class FakeNodeTraverser implements NodeTraverserInterface
{
    public function addVisitor(NodeVisitor $visitor)
    {
        throw new LogicException();
    }

    /**
     * Removes an added visitor.
     *
     * @param NodeVisitor $visitor
     */
    public function removeVisitor(NodeVisitor $visitor)
    {
        throw new LogicException();
    }

    /**
     * Traverses an array of nodes using the registered visitors.
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return Node[] Traversed array of nodes
     */
    public function traverse(array $nodes): array
    {
        throw new LogicException();
    }
}
