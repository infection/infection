<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Visitor;

use Infection\Mutation;
use Infection\MutationInterface;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class MutatorVisitor extends NodeVisitorAbstract
{
    /**
     * @var MutationInterface
     */
    private $mutation;

    public function __construct(MutationInterface $mutation)
    {
        $this->mutation = $mutation;
    }

    public function leaveNode(Node $node)
    {
        $attributes = $node->getAttributes();

        if (!array_key_exists('startTokenPos', $attributes)) {
            return null;
        }

        $mutatedAttributes = $this->mutation->getAttributes();

        $isEqualPosition = $attributes['startTokenPos'] === $mutatedAttributes['startTokenPos'] &&
            $attributes['endTokenPos'] === $mutatedAttributes['endTokenPos'];

        if ($isEqualPosition && $this->mutation->getMutatedNodeClass() === \get_class($node)) {
            var_dump(get_class($this->mutation->getMutatedNode()));
            return $this->mutation->getMutatedNode();
            // TODO STOP TRAVERSING
            // TODO check all built-in visitors, in particular FirstFindingVisitor
            // TODO beforeTraverse - FirstFindingVisitor
            // TODO enterNode instead of leaveNode for '<' mutation to not travers children?
        }
    }
}
