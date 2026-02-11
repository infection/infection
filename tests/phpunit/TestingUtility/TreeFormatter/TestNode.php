<?php

declare(strict_types=1);

namespace Infection\Tests\TestingUtility\TreeFormatter;

final readonly class TestNode
{
    /**
     * @param list<TestNode> $children
     */
    public function __construct(
        public int $id,
        public array $children = [],
    ) {}
}
