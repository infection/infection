<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class ParentConnectorVisitor extends NodeVisitorAbstract
{
    public const PARENT_KEY = 'parent';

    private $stack;

    public function beforeTraverse(array $nodes): void
    {
        $this->stack = [];
    }

    public function enterNode(Node $node): void
    {
        if (!empty($this->stack)) {
            $node->setAttribute(self::PARENT_KEY, $this->stack[\count($this->stack) - 1]);
        }

        $this->stack[] = $node;
    }

    public function leaveNode(Node $node): void
    {
        array_pop($this->stack);
    }
}
