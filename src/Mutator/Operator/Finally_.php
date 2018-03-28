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

class Finally_ extends Mutator
{
    public function mutate(Node $node)
    {
        return new Node\Stmt\Nop();
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Finally_) {
            return false;
        }

        return $this->hasAtLeastOneCatchBlock($node);
    }

    private function hasAtLeastOneCatchBlock(Node $node): bool
    {
        /** @var Node\Stmt\TryCatch $parentNode */
        $parentNode = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        return \count($parentNode->catches) > 0;
    }
}
