<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\PhpParser\Visitor\ParentConnectorVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use function array_unshift;

final class StackSpyVisitor implements NodeVisitor
{
    private $nodes;

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
