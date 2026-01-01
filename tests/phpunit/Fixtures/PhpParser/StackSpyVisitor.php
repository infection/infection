<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use PhpParser\Node;
use PhpParser\NodeVisitor;

final class StackSpyVisitor implements NodeVisitor
{
    private ?array $nodes = null;

    public function beforeTraverse(array $nodes): void
    {
        $this->nodes = [];
    }

    public function enterNode(Node $node): void
    {
        $this->nodes[] = $node;
    }

    public function leaveNode(Node $node): void
    {
    }

    public function afterTraverse(array $nodes): void
    {
    }

    /**
     * @return Node[]
     */
    public function getCollectedNodes(): array
    {
        return $this->nodes;
    }
}
