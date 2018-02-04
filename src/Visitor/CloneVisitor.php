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

class CloneVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $origNode)
    {
        return clone $origNode;
    }
}
