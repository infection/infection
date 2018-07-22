<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 *
 * Adds FullyQualifiedClassName (FQCN) string to class node:
 *      $node->name                    // Plus
 *      $node->fullyQualifiedClassName // Infection\Mutator\Plus
 */
final class FullyQualifiedClassNameVisitor extends NodeVisitorAbstract
{
    private $namespace;

    public function enterNode(Node $node): void
    {
        if ($node instanceof Stmt\Namespace_) {
            $this->namespace = $node->name;
        } elseif ($node instanceof Stmt\ClassLike) {
            $node->fullyQualifiedClassName = $node->name ? Name::concat($this->namespace, $node->name->name) : null;
        }
    }
}
