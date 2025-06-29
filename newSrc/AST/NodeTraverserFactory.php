<?php

declare(strict_types=1);

namespace newSrc\AST;

use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;

final class NodeTraverserFactory
{
    /**
     * @param NodeVisitor[] $nodeVisitors
     */
    public function __construct(
        private array $nodeVisitors,
    ) {
    }

    public function create(): NodeTraverserInterface
    {
        return new NodeTraverser(...$this->nodeVisitors);
    }
}
