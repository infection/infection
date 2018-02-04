<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator;

use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Node;

/**
 * Checks if given node belongs to Interface
 *
 * @author Volodimir Melko <v.melko28@gmail.com>
 */
trait InterfaceParentTrait
{
    private function isBelongsToInterface(Node $node): bool
    {
        $parentNode = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        if ($parentNode instanceof Node\Stmt\Interface_) {
            return true;
        }

        if ($parentNode instanceof Node) {
            return $this->isBelongsToInterface($parentNode);
        }

        return false;
    }
}
