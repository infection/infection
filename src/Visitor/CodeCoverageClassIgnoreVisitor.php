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
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class CodeCoverageClassIgnoreVisitor extends NodeVisitorAbstract
{
    private $namespace;

    public function enterNode(Node $node)
    {
        if ($node instanceof Stmt\Namespace_) {
            $this->namespace = $node->name;
        } elseif ($node instanceof Stmt\ClassLike) {
            if (!$node->name) {
                return null;
            }

            /** @var Name $fullyQualifiedClassName */
            $fullyQualifiedClassName = Name::concat($this->namespace, $node->name->name);

            $reflectionClass = new \ReflectionClass($fullyQualifiedClassName->toString());

            $docComment = $reflectionClass->getDocComment();

            if ($docComment === false) {
                return null;
            }

            if (strpos($docComment, '@codeCoverageIgnore') !== false) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
        }
    }
}
