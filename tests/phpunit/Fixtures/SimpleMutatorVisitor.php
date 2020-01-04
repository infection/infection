<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class SimpleMutatorVisitor extends NodeVisitorAbstract
{
    /**
     * @var SimpleMutation
     */
    private $mutation;

    public function __construct(SimpleMutation $mutation)
    {
        $this->mutation = $mutation;
    }

    public function leaveNode(Node $node)
    {
        $mutator = $this->mutation->getMutator();

        if ($mutator->shouldMutate($node)) {
            return $this->mutation->getMutatedNode();
        }
    }
}
