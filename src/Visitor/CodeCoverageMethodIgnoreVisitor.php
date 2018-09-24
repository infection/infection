<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Visitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class CodeCoverageMethodIgnoreVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\ClassMethod) {
            return null;
        }

        /** @var \ReflectionClass $reflection */
        $reflection = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        $method = $reflection->getMethod($node->name->toString());

        $docComment = $method->getDocComment();

        if ($docComment === false) {
            return null;
        }

        if (strpos($docComment, '@codeCoverageIgnore') !== false) {
            /*
             * This is a workaround to "disable" this Node from mutation.
             *
             * When PHP-Parser's NodeTraverser::DONT_TRAVERSE_CHILDREN is returned, subsequent Visitors still process
             * current Node (enterNode(), leaveNode()), but without its children. This lead to class method to be
             * mutated, while it shouldn't be.
             */
            $node->setAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);
            $node->setAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY, false);

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }
}
