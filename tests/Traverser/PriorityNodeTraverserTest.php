<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Traverser;

use Infection\Traverser\PriorityNodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PriorityNodeTraverserTest extends TestCase
{
    public function test_it_sorts_visitors_by_priorites(): void
    {
        $traverser = new PriorityNodeTraverser();

        $callOrder = [];

        $traverser->addVisitor($this->createVisitor($callOrder, 20), 20);
        $traverser->addVisitor($this->createVisitor($callOrder, 30), 30);
        $traverser->addVisitor($this->createVisitor($callOrder, 5), 5);
        $traverser->addVisitor($this->createVisitor($callOrder, 10), 10);

        $traverser->traverse([]);

        $this->assertSame([30, 20, 10, 5], $callOrder);
    }

    public function test_it_does_not_allow_duplicater_priorities(): void
    {
        $traverser = new PriorityNodeTraverser();

        $callOrder = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Priority 20 is already used');

        $traverser->addVisitor($this->createVisitor($callOrder, 20), 20);
        $traverser->addVisitor($this->createVisitor($callOrder, 20), 20);
    }

    private function createVisitor(array &$callOrder, int $priority): NodeVisitor
    {
        return new class($callOrder, $priority) extends NodeVisitorAbstract {
            public $callOrder;
            public $priority;

            public function __construct(array &$callOrder, int $priority)
            {
                $this->callOrder = &$callOrder;
                $this->priority = $priority;
            }

            public function beforeTraverse(array $nodes): void
            {
                $this->callOrder[] = $this->priority;
            }
        };
    }
}
