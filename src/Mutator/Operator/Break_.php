<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Operator;

use Infection\Mutator\Util\Mutator;
use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Node;

/**
 * @internal
 */
final class Break_ extends Mutator
{
    /**
     * Replaces "break;" with "continue;"
     *
     * @param Node $node
     *
     * @return Node\Stmt\Continue_
     */
    public function mutate(Node $node)
    {
        yield new Node\Stmt\Continue_();
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Break_) {
            return false;
        }

        $parentNode = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        return !$parentNode instanceof Node\Stmt\Case_;
    }
}
