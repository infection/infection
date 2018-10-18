<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Traverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class PriorityNodeTraverser extends NodeTraverser
{
    public function addVisitor(NodeVisitor $visitor, int $priority = 1): void
    {
        Assert::keyNotExists($this->visitors, $priority, sprintf('Priority %d is already used', $priority));

        $this->visitors[$priority] = $visitor;

        krsort($this->visitors);
    }
}
